# Inside Cancun — Booking Engine

CRM + Motor de Reservas + CMS de Blog para Inside Cancun, con arquitectura
preparada para integrarse posteriormente con proveedores externos (Bokun,
Viator, GetYourGuide) sin acoplar el dominio interno a ninguno de ellos.

## Estado del proyecto

**Fase actual: FASE 1 — Infraestructura** (andamiaje generado, pendiente de verificación local — ver `docs/development.md`)

## Estructura

```
inside-cancun/
├── backend/     # Laravel 13 (API REST + Filament admin)
├── frontend/    # Next.js (web pública)
├── docs/        # Documentación viva del proyecto
├── docker/      # Docker Compose / infraestructura local
└── README.md
```

## Documentación

- [`docs/architecture.md`](docs/architecture.md) — Arquitectura general, principios, riesgos y decisiones pendientes.
- [`docs/database.md`](docs/database.md) — Modelo conceptual de base de datos.
- [`docs/development.md`](docs/development.md) — Cómo levantar y verificar la infraestructura local (Fase 1).
- `docs/api.md`, `docs/booking-flow.md`, `docs/availability.md`,
  `docs/integrations.md`, `docs/deployment.md` — se crearán/actualizarán
  conforme avancen las fases correspondientes.

## Metodología de desarrollo

El proyecto se construye **fase por fase**, siguiendo el plan definido en
`docs/architecture.md` (sección de roadmap). Cada fase:

1. Se implementa de forma aislada.
2. Se prueba (migraciones, endpoints, interfaz).
3. Se documenta.
4. Se presenta con un reporte estandarizado.
5. Se detiene hasta recibir aprobación explícita antes de continuar.

## Stack

- **Backend**: Laravel 13, PHP 8.4+, PostgreSQL, Redis, Sanctum, Filament.
- **Frontend**: Next.js, React, TypeScript, Tailwind CSS.
- **Infraestructura**: Docker, Docker Compose, Git.
