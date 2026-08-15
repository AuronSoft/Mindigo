# Mindigo local development with isolated Docker images

This environment runs the complete LMS without Laragon, host PHP, Composer,
Node.js, MySQL or Redis. Application code and frontend assets run from the same
image on the container's Linux filesystem. This avoids slow Windows bind mounts
and prevents Vite manifests from diverging between Laravel and Nginx.

## Services and isolation

| Service | Responsibility | Host port |
|---|---|---|
| `nginx` | HTTP entry point | `127.0.0.1:8080` |
| `app` | Laravel PHP-FPM | None |
| `mysql` | MySQL 8.4 | None |
| `redis` | Cache, sessions and queues | None |
| `queue` | Background jobs | None |
| `scheduler` | Scheduled commands | None |
| `reverb` | Realtime WebSocket server | `127.0.0.1:8082` |

MySQL and Redis are reachable only through the private `backend` network. Only
Nginx and Reverb publish loopback ports. Database data, Redis data, uploads and
public assets live in project-scoped named volumes.

## Requirements

Start Docker Desktop and verify:

```powershell
docker version
docker compose version
```

Do not run `php artisan serve`, `composer dev`, local MySQL or local Redis.

## Daily command reference

Run every command from the repository root. The normal development day only
needs the start and stop commands below.

| Operation | Command |
|---|---|
| First start or Docker configuration changed | `docker compose -f docker-compose.dev.yml up -d --build` |
| Normal daily start | `docker compose -f docker-compose.dev.yml up -d` |
| Open the application | `http://127.0.0.1:8080` |
| Check all services | `docker compose -f docker-compose.dev.yml ps` |
| Follow all logs | `docker compose -f docker-compose.dev.yml logs -f` |
| Follow application logs | `docker compose -f docker-compose.dev.yml logs -f app nginx` |
| Follow background/realtime logs | `docker compose -f docker-compose.dev.yml logs -f queue scheduler reverb vite` |
| Restart all services | `docker compose -f docker-compose.dev.yml restart` |
| Restart one service | `docker compose -f docker-compose.dev.yml restart app` |
| Stop and preserve data | `docker compose -f docker-compose.dev.yml down` |

The standard daily flow is:

```powershell
# Start the complete LMS
docker compose -f docker-compose.dev.yml up -d

# Confirm that services are running
docker compose -f docker-compose.dev.yml ps

# Develop in the IDE, then stop at the end of the day
docker compose -f docker-compose.dev.yml down
```

Do not run any of these host commands alongside the stack:

```text
php artisan serve
npm run dev
php artisan queue:work
php artisan schedule:work
php artisan reverb:start
redis-server
```

Their corresponding processes are already managed by Docker Compose.

## First start

```powershell
docker compose -f docker-compose.dev.yml config
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml ps
```

Open <http://127.0.0.1:8080>. The `app` service waits for MySQL, runs migrations,
seeds a fresh development database once and creates the storage link.

Seed an empty database when required:

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan db:seed
```

## Development workflow

PHP, Blade, CSS and JavaScript are packaged together to keep the runtime
consistent. You do not run `php artisan serve` or `npm run dev` on the host.

Start the environment once:

```powershell
docker compose -f docker-compose.dev.yml up -d --build --force-recreate
```

Rebuild the app image only after changing `Dockerfile.dev`, PHP extensions or
the Docker entrypoint:

```powershell
docker compose -f docker-compose.dev.yml build app
docker compose -f docker-compose.dev.yml up -d --force-recreate app queue scheduler reverb
```

When `composer.lock` changes, refresh the isolated vendor volume:

```powershell
docker compose -f docker-compose.dev.yml run --rm app composer install
```

## Commands inside Docker

```powershell
docker compose -f docker-compose.dev.yml logs -f app nginx queue reverb vite
docker compose -f docker-compose.dev.yml exec app php artisan about
docker compose -f docker-compose.dev.yml exec app php artisan migrate:status
docker compose -f docker-compose.dev.yml exec app composer test
docker compose -f docker-compose.dev.yml exec app ./vendor/bin/pint --test
docker compose -f docker-compose.dev.yml exec app bash
docker compose -f docker-compose.dev.yml exec mysql mysql -umindigo -pmindigo_local mindigo
```

Common Laravel operations must also run inside `app`:

```powershell
# Run new migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate

# Create demo data
docker compose -f docker-compose.dev.yml exec app php artisan db:seed

# Clear Laravel caches
docker compose -f docker-compose.dev.yml exec app php artisan optimize:clear

# Run one test file
docker compose -f docker-compose.dev.yml exec app php artisan test tests/Feature/ExampleTest.php
```

## When a rebuild is required

Normal PHP, Blade, CSS and JavaScript edits do not require a rebuild. Rebuild
only when changing one of the following:

- `Dockerfile.dev`;
- PHP extensions or operating-system packages;
- `docker/entrypoint.sh`;
- build-time Vite configuration;
- Composer dependencies packaged into the base image.

Use:

```powershell
docker compose -f docker-compose.dev.yml up -d --build
```

If only runtime Compose values changed, recreate without rebuilding:

```powershell
docker compose -f docker-compose.dev.yml up -d --force-recreate
```

## Troubleshooting workflow

When the application does not start, use this order:

```powershell
# 1. Find the unhealthy or restarting service
docker compose -f docker-compose.dev.yml ps

# 2. Read its latest logs (replace app when necessary)
docker compose -f docker-compose.dev.yml logs --tail=100 app

# 3. Validate the resolved Compose configuration
docker compose -f docker-compose.dev.yml config --quiet

# 4. Recreate services after fixing configuration
docker compose -f docker-compose.dev.yml up -d --force-recreate

# 5. Verify the Laravel health endpoint
curl.exe -I http://127.0.0.1:8080/up
```

For realtime issues, inspect both Reverb and queue logs:

```powershell
docker compose -f docker-compose.dev.yml logs --tail=100 reverb queue
```

For frontend asset issues:

```powershell
docker compose -f docker-compose.dev.yml build app
docker compose -f docker-compose.dev.yml up -d --force-recreate app nginx
```

## Health verification

```powershell
docker compose -f docker-compose.dev.yml ps
docker compose -f docker-compose.dev.yml exec redis redis-cli ping
curl.exe -I http://127.0.0.1:8080/up
docker compose -f docker-compose.dev.yml logs --tail=50 reverb
```

Expected: `app`, `mysql` and `redis` are healthy; Nginx, queue, scheduler and
Reverb are running; `/up` returns HTTP 200. Always access the dev application
through `http://127.0.0.1:8080` so browser cookies use one consistent host.

## Ports

Override the web port if `8080` is occupied:

```powershell
$env:DEV_HTTP_PORT = "8090"
docker compose -f docker-compose.dev.yml up -d
```

Reverb defaults to `8082`. When overriding `DEV_REVERB_PORT`, rebuild the app
image so Compose passes the same value into the Vite build automatically.

## Stop and reset

```powershell
# Stop while preserving database and uploads
docker compose -f docker-compose.dev.yml down

# Permanently remove all local Docker data for this project
docker compose -f docker-compose.dev.yml down --volumes
```

The second command is destructive and should only be used for a clean reset.

## Security boundaries

- The Compose project is named `mindigo-dev`.
- Source code and built assets are packaged in images; `.env`, Docker credentials
  and the Docker socket are not mounted into application services.
- MySQL and Redis have no host port mapping.
- Runtime data is separated into named volumes.
- Embedded credentials are local-only and must never be reused in production.
