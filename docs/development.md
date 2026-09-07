# Inside Cancun — Guía de Desarrollo Local (Fase 1)

> Estos pasos deben ejecutarse en tu máquina o CI, no en este entorno de
> generación de código, ya que aquí no hay Docker/PHP/Composer disponibles
> ni acceso de red a packagist.org.

## 0. Requisitos

- Docker y Docker Compose instalados.
- Git.

## 1. Clonar / colocar el proyecto

Coloca la carpeta `inside-cancun/` (backend, frontend, docs, docker) en tu
máquina e inicializa git si aún no existe:

```bash
cd inside-cancun
git init
git add .
git commit -m "chore: fase 0 - arquitectura y documentación inicial"
```

## 2. Crear el proyecto Laravel dentro de backend/

El contenido de `backend/` en este entregable **solo trae `.env.example`**;
el esqueleto real de Laravel se genera con Composer (requiere red a
packagist.org):

```bash
cd backend
composer create-project laravel/laravel . "13.*"
cp .env.example .env   # sobreescribe con el .env.example ya provisto si lo pediste así
```

> Si ya tenías un `.env.example` personalizado (el que viene en este
> entregable), no lo sobrescribas: solo copia sus valores dentro del
> `.env` que Laravel generó, o cópialo directamente sobre `.env` y
> luego ejecuta `php artisan key:generate`.

## 3. Instalar dependencias del proyecto

```bash
composer require laravel/sanctum
composer require filament/filament:"^3.0"

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan filament:install --panels
```

## 4. Configurar Docker

Desde la raíz del proyecto:

```bash
cp docker/.env.example docker/.env
cd docker
docker compose --env-file .env up -d --build
```

Esto levanta:

| Servicio | Contenedor | Puerto host |
|---|---|---|
| Nginx (web) | inside_cancun_nginx | 8080 |
| PHP-FPM | inside_cancun_app | (interno 9000) |
| PostgreSQL | inside_cancun_postgres | 5432 |
| Redis | inside_cancun_redis | 6379 |

## 5. Generar app key y correr migraciones base

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

## 6. Crear usuario administrador para Filament

```bash
docker compose exec app php artisan make:filament-user
```

## 7. Checklist de verificación de Fase 1

Ejecuta y confirma cada uno de estos puntos (son los "resultados esperados"
definidos en el prompt original):

- [ ] `docker compose ps` muestra los 4 contenedores en estado `Up`.
- [ ] `http://localhost:8080` responde con la página de bienvenida de Laravel.
- [ ] `docker compose exec app php artisan migrate:status` conecta correctamente a PostgreSQL.
- [ ] `docker compose exec app php artisan tinker` → `Redis::connection()->ping()` responde `+PONG`.
- [ ] `http://localhost:8080/admin` carga el login de Filament.
- [ ] Login con el usuario creado en el paso 6 funciona.
- [ ] `php artisan route:list` incluye las rutas de Sanctum.

Marca este checklist (o repórtame los resultados) para que la Fase 1 quede
formalmente cerrada con evidencia real, no solo con el código generado.

## 7.1 Verificar migraciones de Fase 2

Una vez tengas los contenedores arriba (paso 4) y `.env` configurado:

```bash
# copia las migraciones de este entregable a tu proyecto Laravel real
cp -r database/migrations/*.php <tu-proyecto-laravel>/database/migrations/

docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:status
```

Para probar la protección anti-overbooking a nivel de base de datos:

```bash
docker compose exec postgres psql -U inside_cancun -d inside_cancun -c "\d availability_slots"
# debe listar: chk_capacity_not_exceeded CHECK (capacity_booked <= capacity_total)
```

Y para confirmar que el rollback funciona limpio:

```bash
docker compose exec app php artisan migrate:rollback
docker compose exec app php artisan migrate
```

Repórtame el resultado de `docs/database.md` → "Checklist de verificación
de Fase 2" para cerrarla formalmente.

## 7.2 Verificar Fase 3 (Tours)

```bash
# copia modelos, recursos Filament y tests a tu proyecto Laravel real
cp -r app/Models/*.php <tu-proyecto-laravel>/app/Models/
cp -r app/Filament <tu-proyecto-laravel>/app/
cp -r tests/Feature/*.php <tu-proyecto-laravel>/tests/Feature/

docker compose exec app composer require filament/filament:"^3.0"   # si no lo hiciste ya
docker compose exec app php artisan filament:install --panels       # si no lo hiciste ya
docker compose exec app php artisan storage:link                    # necesario para FileUpload de imágenes

docker compose exec app php artisan test --filter=Tour
```

Verificación manual en el navegador:

1. Entra a `http://localhost:8080/admin`.
2. Ve a **Tours → Categorías** y crea 2-3 categorías.
3. Ve a **Tours → Tours**, crea un tour completo: nombre (verifica que el
   slug se autogenera), descripción, duración, categorías, estado, SEO.
4. Dentro del tour creado, entra a la pestaña/relation manager **Imágenes**
   y sube 2 imágenes, marca una como portada, confirma que la otra se
   desmarca automáticamente.
5. Entra a **Variantes y precios**, crea una variante ("Adulto") y agrégale
   un precio dentro del mismo formulario (Repeater anidado).
6. Verifica que la tabla de listado de tours muestra la portada, categorías,
   estado con color, y que los filtros (estado, destacado, categoría,
   papelera) funcionan.
7. Borra un tour (soft delete), confirma que desaparece del listado normal
   pero aparece con el filtro "Trashed", y que se puede restaurar.

## 8. Problemas comunes

- **Error de conexión a PostgreSQL**: revisa que `DB_HOST=postgres` (nombre
  del servicio en docker-compose, no `localhost`) dentro de `backend/.env`.
- **Permisos en `storage/` y `bootstrap/cache/`**: si ves errores de
  escritura, ejecuta `docker compose exec app chmod -R 775 storage bootstrap/cache`.
- **Extensión `pdo_pgsql` no encontrada**: confirma que reconstruiste la
  imagen (`--build`) después de cualquier cambio al Dockerfile.
