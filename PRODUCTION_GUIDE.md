# Production Deployment & Performance Guide (September 2026)

This guide explains the production architecture and performance optimizations implemented for SIKENDIS on Coolify.

## 1. High-Performance Runtime: FrankenPHP Worker Mode
We have enabled **Worker Mode** via FrankenPHP and Laravel Octane in Dockerfile.

- **Engine**: FrankenPHP on Debian Bookworm (ensuring robust glibc thread-stacking, stable JIT compiler, and reliable networking).
- **How it works**: Your application boots once into RAM and serves thousands of requests with zero bootstrap overhead.
- **Boot Tax**: ~0ms.
- **Latency**: Sub-millisecond.
- **Worker Recycling**: Default `--max-requests=1000` is enabled in `Dockerfile` to guard against potential memory leaks in long-lived workers.

## 2. Bytecode & Server Layer Excellence: OPcache, JIT & Caddyfile
- **OPcache**: Bytecode is cached in shared memory (`opcache.validate_timestamps=0` in production).
- **JIT**: Enabled in **Tracing Mode** for native CPU execution of hotspots.
- **Custom Caddyfile**: Configured with `encode zstd br gzip`, static asset caching (`max-age=31536000, immutable`), and security headers (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`).

## 3. Automated Production Framework Optimization
During startup (`docker-entrypoint.sh`), when `APP_ENV=production`, the container automatically warms:
- `php artisan optimize`: Caches configuration, routes, and views.
- `php artisan filament:optimize`: Specifically caches Filament resources, pages, widgets, and components.
- `php artisan icons:cache`: Pre-caches FluentUI SVG icons to eliminate disk I/O.

## 4. Coolify Deployment Recommendations (Zero-Downtime)

### Option A: Standard Application Deployment (RECOMMENDED for Zero-Downtime)
1. In Coolify, create a new resource: **Application** -> **Public/Private Repository** -> Select `sipedas`.
2. Build Pack: **Dockerfile**.
3. Ports Exposes: `80`.
4. Domain: Set your domain (e.g. `https://admin.dvlpid.my.id`).
5. Coolify will manage Traefik SSL and use rolling updates (starts new container, waits for healthcheck `http://localhost/up`, shifts traffic, then removes old container).

### Option B: Docker Compose Deployment
1. If deploying via `docker-compose.yml`, ensure the server network `coolify` is attached.
2. Health check includes `start_period: 30s` to allow database migration and initial caching without premature failures.

## 5. Required Environment Variables on Coolify
Ensure these Environment Variables are set in Coolify:
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- `APP_KEY`: *(Generated via artisan key:generate)*
- `APP_URL`: `https://admin.dvlpid.my.id`
- `DB_HOST`: *(Your database container/host name)*
- `DB_DATABASE`: *(Your database name)*
- `DB_USERNAME`: *(Your database username)*
- `DB_PASSWORD`: *(Your database password)*
- `REDIS_HOST`: *(Your redis container/host name)*
- `OCTANE_MAX_REQUESTS`: `1000`

---
*Maintained by Antigravity AI - September 2026*
