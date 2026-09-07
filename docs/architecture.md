# INSIDE CANCUN — Arquitectura General (Fase 0)

## 1. Visión

Sistema centralizado (CRM + Motor de Reservas + CMS) que administra el
inventario turístico de Inside Cancun de forma **independiente de cualquier
proveedor externo** (Bokun, Viator, GetYourGuide, etc.). Estos proveedores
serán consumidos más adelante a través de una capa de integración basada en
adapters, sin que su modelo de datos contamine el dominio interno.

## 2. Diagrama de arquitectura

```
                    INSIDE CANCUN WEB (Next.js)
                              |
                          REST API (v1)
                              |
                    INSIDE CANCUN BACKEND (Laravel 13)
                              |
          +-------------------+--------------------+
          |                   |                     |
         CRM           BOOKING ENGINE               CMS
          |                   |                     |
          |     Availability / Inventory / Payments |
          +-------------------+--------------------+
                              |
                         PostgreSQL + Redis
                              |
                      Integration Layer (adapters)
                              |
          +-------------------+--------------------+
          |                   |                     |
        Bokun               Viator                 GYG
```

## 3. Principios rectores

1. **Dominio propio primero.** El modelo de `tours`, `bookings`,
   `customers`, etc. es la fuente de verdad. Ningún ID externo (Bokun,
   Viator, GYG) es nunca el ID principal de una entidad.
2. **Adapters, no acoplamiento.** Cada proveedor externo se integra vía
   `Client → Provider (implementa TourProviderInterface) → Mapper → Dominio`.
   Nunca al revés.
3. **Frontend tonto, backend con la lógica.** Next.js solo consume la API
   de Laravel; nunca credenciales de proveedores ni lógica de negocio.
4. **Concurrencia segura desde el día 1.** El motor de disponibilidad debe
   prevenir overbooking mediante bloqueos/transacciones a nivel de base de
   datos, con pruebas automatizadas obligatorias.
5. **Modularidad sin sobreingeniería.** Se usan Services, Actions, DTOs,
   Jobs, Events, Policies y Form Requests donde aportan valor real; se evita
   crear Repositories o abstracciones "por si acaso".
6. **Preparado para escalar, no escalado de más.** La arquitectura deja
   espacio para multi-tenant, multi-moneda y multi-proveedor, pero no se
   implementa multi-tenancy completo en el MVP.

## 4. Estructura del repositorio

```
inside-cancun/
├── backend/            # Laravel 13 (API + Filament admin)
├── frontend/            # Next.js (web pública)
├── docs/                # Documentación viva del proyecto
├── docker/              # docker-compose, Dockerfiles, configs
└── README.md
```

## 5. Estructura modular del backend (propuesta)

```
backend/app/
├── Domain/
│   ├── Tours/            (Models, Actions, Services, DTOs)
│   ├── Availability/
│   ├── Inventory/
│   ├── Booking/
│   ├── CRM/
│   ├── Blog/
│   ├── Payments/
│   └── Integrations/
│       ├── Bokun/
│       ├── Viator/
│       └── GetYourGuide/
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   └── Resources/
├── Filament/            (Resources del panel admin)
├── Jobs/
├── Events/
├── Listeners/
├── Policies/
└── Exceptions/
```

Cada carpeta dentro de `Domain/{Modulo}` seguirá, cuando aplique:
`Models/`, `Actions/`, `Services/`, `DTOs/`, `Events/`, `Exceptions/`.

## 6. Convenciones de código

- PSR-12 + reglas de Laravel Pint por defecto.
- Nombres de tablas en `snake_case` plural; modelos en `PascalCase` singular.
- Un Action = una operación de negocio (`CreateBookingAction`,
  `ConfirmBookingAction`, `CancelBookingAction`).
- Los Services orquestan; los Actions ejecutan una unidad de trabajo; los
  Jobs se usan para trabajo asíncrono (emails, sync con proveedores).
- Toda escritura sensible a concurrencia (disponibilidad/inventario) pasa
  por una capa de Service con transacción explícita y bloqueo pesimista
  (`lockForUpdate()`), nunca directo desde el controller.
- Commits: Conventional Commits (`feat:`, `fix:`, `docs:`, `test:`, `chore:`).

## 7. Riesgos técnicos identificados

| Riesgo | Impacto | Mitigación propuesta |
|---|---|---|
| Overbooking por condiciones de carrera | Alto | Transacciones + `lockForUpdate` en PostgreSQL, tests de concurrencia (Fase 4-5) |
| Diseño de disponibilidad demasiado rígido para futuros proveedores | Medio | Modelar `availability` desacoplado de `provider_products` desde el inicio |
| Mapeo incorrecto Bokun/Viator/GYG → dominio interno | Alto | No implementar integraciones hasta que el dominio esté maduro (Fase 10+) |
| Credenciales de proveedores expuestas | Crítico | Nunca en frontend; `.env` + posible vault más adelante |
| Cambios de esquema costosos una vez con datos reales | Medio | ERD revisado en Fase 2 antes de escribir migraciones |
| Multi-moneda/multi-idioma añadido tarde | Bajo-Medio | Dejar columnas/estructura preparada sin implementar lógica completa aún |

## 8. Decisiones pendientes (requieren tu input antes de programar)

1. **Proveedor de pagos**: ¿Stripe, Conekta, un procesador local mexicano, o más de uno?
2. **Multi-moneda desde el inicio**: ¿operamos solo en USD/MXN o se requiere flexibilidad total desde el día 1?
3. **Autenticación del panel admin**: ¿Filament con roles/permissions vía Spatie, o algo más simple al inicio?
4. **Hosting/infraestructura objetivo**: ¿VPS propio, un proveedor cloud (AWS/DigitalOcean/Hetzner), o aún no decidido? (afecta Fase 1 - Docker)
5. **Idiomas del contenido (Tours/Blog)**: ¿español e inglés desde el MVP, o solo español por ahora con estructura preparada?
6. **Volumen esperado de reservas concurrentes**: ayuda a dimensionar Redis/colas desde el inicio.

No es necesario resolver todo ahora — puedes responderlas conforme avancemos, pero las #1, #3 y #5 conviene definirlas antes de la Fase 2 (Database).

## 9. Roadmap (referencia — ya definido en el prompt original)

Fase 0 Arquitectura → Fase 1 Infraestructura → Fase 2 Database → Fase 3
Tours → Fase 4 Availability/Inventory → Fase 5 Booking Engine → Fase 6 CRM →
Fase 7 CMS → Fase 8 API → Fase 9 Next.js → Fase 10 Bokun → Fase 11 Viator →
Fase 12 GetYourGuide → Fase 13 Payments → Fase 14 Notificaciones → Fase 15
Reports.

Cada fase termina con el reporte estandarizado (objetivo, funcionalidades,
archivos, migraciones, endpoints, tests, cómo ejecutar/probar, problemas,
próxima fase) y se detiene hasta recibir `APROBADO — CONTINÚA`.
