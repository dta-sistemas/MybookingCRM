# INSIDE CANCUN — Modelo de Base de Datos

> Sección "Fase 0" = modelo conceptual inicial. Sección "Fase 2" (al final
> de este documento) = esquema real implementado en
> `backend/database/migrations/`.

## 1. Entidades principales y relaciones

```
users ──< roles/permissions (Spatie, por decidir)

tours ──< tour_images
tours ──< tour_categories >── categories
tours ──< tour_variants
tour_variants ──< prices
tours ──< tour_resources >── resources
tours ──< availability_slots
availability_slots ──< inventory_holds

tours ──< provider_products >── providers

customers ──< leads
leads ──< lead_activities
customers ──< bookings

bookings ──< booking_items >── tour_variants
bookings ──< passengers
bookings ──< payments
bookings ── availability_slots (reserva sobre un slot específico)

blog_posts ──< blog_categories
blog_posts ──< blog_tags

audit_logs (polimórfico, referencia cualquier entidad)
notifications (polimórfico)
```

## 2. Núcleo: Tour vs Producto Externo

```
┌─────────────┐        ┌──────────────────────┐        ┌────────────┐
│   tours     │ 1    N │  provider_products    │ N    1 │ providers  │
│─────────────│───────>│──────────────────────│<───────│────────────│
│ id (PK)     │        │ id (PK)               │        │ id (PK)    │
│ name        │        │ tour_id (FK)          │        │ name       │
│ slug        │        │ provider_id (FK)      │        │ slug       │
│ description │        │ external_id           │        │ is_active  │
│ duration    │        │ external_data (json)  │        └────────────┘
│ status      │        │ sync_status           │
└─────────────┘        │ last_synced_at        │
                        └──────────────────────┘
```

El `tour.id` (interno) es siempre el que usan `bookings`, `availability`,
`prices`, etc. `provider_products.external_id` es solo una referencia de
mapeo hacia el proveedor externo, nunca la clave usada internamente.

## 3. Disponibilidad e inventario (núcleo crítico)

```
availability_slots
├── id (PK)
├── tour_id (FK)
├── date
├── start_time
├── capacity_total
├── capacity_booked   -- se actualiza dentro de transacción con lock
└── status (open, closed, blackout)

resources
├── id (PK)
├── name (ATV, Boat, Van, Guide...)
└── type

tour_resources (pivot tour <-> resources, con cantidad requerida)

inventory_holds        -- reservas temporales antes de confirmar pago
├── id (PK)
├── availability_slot_id (FK)
├── quantity
├── expires_at
└── booking_id (nullable hasta confirmar)
```

La prevención de overbooking se apoya en:
- `capacity_booked <= capacity_total` como invariante de negocio (no solo
  constraint de DB, sino verificado dentro de una transacción con
  `lockForUpdate()` sobre la fila de `availability_slots`).
- `inventory_holds` con expiración para reservas temporales no pagadas.

## 4. Reservas

```
bookings
├── id (PK)
├── customer_id (FK)
├── tour_id (FK)
├── availability_slot_id (FK)
├── status (PENDING, PAYMENT_PENDING, CONFIRMED, CANCELLED, COMPLETED, NO_SHOW)
├── total_amount
├── currency
└── created_at / updated_at

booking_items
├── id (PK)
├── booking_id (FK)
├── tour_variant_id (FK)
├── quantity
└── unit_price

passengers
├── id (PK)
├── booking_id (FK)
├── full_name
├── age_type (adult/child/infant)
└── document (opcional)

payments
├── id (PK)
├── booking_id (FK)
├── amount
├── currency
├── status
├── provider (stripe/conekta/...)
└── provider_reference   -- NUNCA se guardan datos de tarjeta
```

## 5. CRM

```
customers ──< leads ──< lead_activities
customers ──< bookings
customers ──< notes
```

`lead_activities` funciona como fuente de la `customer timeline` (no se
requiere una tabla separada de "timeline"; se puede componer a partir de
`lead_activities` + eventos de `bookings` + `payments`).

## 6. CMS

```
blog_posts
├── id, title, slug, excerpt, content, featured_image
├── status (draft, scheduled, published)
├── published_at
├── meta_title, meta_description, canonical_url
└── author_id (FK -> users)

blog_categories, blog_tags  -- relación N:N con blog_posts
```

## 7. Decisiones de diseño a validar en Fase 2

- ¿`prices` vive en `tour_variants` directamente o en tabla separada para
  soportar vigencias/temporadas (alta/baja temporada)? → Recomendado: tabla
  separada `prices` con `valid_from`/`valid_until` desde el inicio, para no
  reescribir en el futuro.
- ¿`availability_slots` se genera de forma perezosa (on-demand) o se
  precalcula por rango de fechas (ej. próximos 90 días) vía scheduler?
- Índices únicos recomendados desde ya: `tours.slug`, `blog_posts.slug`,
  `(provider_id, external_id)` en `provider_products`.

---

## Fase 2 — Esquema implementado (26 migraciones)

Orden de creación (respeta dependencias de FK):

1. `users`, `password_reset_tokens`, `sessions`
2. `categories`
3. `tours`
4. `category_tour` (pivot)
5. `tour_images`
6. `tour_variants`
7. `prices`
8. `resources`
9. `resource_tour` (pivot)
10. `availability_slots` — incluye `CHECK (capacity_booked <= capacity_total)`
11. `inventory_holds` (sin `booking_id` todavía)
12. `providers`
13. `provider_products` — `unique(provider_id, external_id)`
14. `customers`
15. `leads`
16. `lead_activities`
17. `customer_notes`
18. `bookings`
19. `add_booking_id_to_inventory_holds` (alter, ahora que `bookings` existe)
20. `booking_items`
21. `passengers`
22. `payments`
23. `blog_categories`
24. `blog_tags`
25. `blog_posts`
26. `blog_post_tag` (pivot)

### Decisiones de diseño aplicadas

- **`prices`** es tabla independiente de `tour_variants`, con
  `valid_from`/`valid_until`, para soportar temporadas sin reescribir el
  esquema (según lo anotado en la sección de decisiones de Fase 0).
- **Prevención de overbooking (doble defensa):**
  - A nivel de aplicación: toda escritura sobre `availability_slots.capacity_booked`
    debe ocurrir dentro de una transacción con `lockForUpdate()` (se
    implementa en Fase 4/5).
  - A nivel de base de datos: `CHECK (capacity_booked <= capacity_total)`
    como red de seguridad final — si algo falla en la capa de aplicación,
    PostgreSQL rechaza el INSERT/UPDATE inválido.
- **`provider_products.external_id`** nunca es la PK de `tours`; es un
  mapeo N:1 hacia el tour interno, con `unique(provider_id, external_id)`
  para que un mismo proveedor nunca duplique el mismo producto externo.
- **Borrado de registros financieros/operativos**: `bookings`, `payments` y
  `booking_items` usan `restrictOnDelete()` sobre sus relaciones críticas
  (customer, tour, availability_slot) — no se puede borrar un tour o
  cliente si tiene reservas asociadas. `tours` y `blog_posts` usan
  `softDeletes()` en vez de borrado físico.
- **Roles de usuario**: se agregó una columna simple `role` (`admin`/`staff`)
  en `users` en lugar de instalar Spatie Permission todavía — la decisión
  pendiente #3 de Fase 0 sigue abierta; si se adopta Spatie más adelante,
  la migración de roles/permissions se añade sin romper lo existente.
- **`customers.email`** no es único a propósito: es común que agencias u
  hoteles reserven a nombre de varios huéspedes usando el mismo correo de
  contacto. Se indexó para búsquedas rápidas, pero la deduplicación de
  clientes (si se requiere) se resolverá con lógica de negocio, no con un
  constraint rígido.

### Pendiente de verificación (requiere tu entorno con PHP/Composer/Docker)

Este entorno de generación de código no puede ejecutar
`php artisan migrate` contra PostgreSQL real (ver limitación reportada en
Fase 1). Las migraciones fueron revisadas manualmente (sintaxis Laravel,
balance de llaves/paréntesis, orden de dependencias de FK, nombres de
tablas/columnas consistentes con el resto del proyecto), pero **no han sido
ejecutadas**. Ver checklist de verificación al final de este documento.

### Checklist de verificación de Fase 2

- [ ] `php artisan migrate` corre sin errores sobre PostgreSQL limpio.
- [ ] `php artisan migrate:rollback` revierte las 26 migraciones sin errores.
- [ ] `\d availability_slots` en `psql` muestra el constraint `chk_capacity_not_exceeded`.
- [ ] Intentar insertar manualmente `capacity_booked > capacity_total` en `availability_slots` falla con violación de CHECK.
- [ ] Insertar dos `provider_products` con mismo `provider_id` + `external_id` falla por `unique`.
- [ ] Borrar un `tour` con `bookings` asociados falla (restrictOnDelete).
