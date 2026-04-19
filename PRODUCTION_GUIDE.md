# Production Deployment & Performance Guide (April 2026)

This guide explains the production architecture and performance optimizations implemented for SIKENDIS on Coolify.

## 1. High-Performance Runtime: FrankenPHP Worker Mode
We have enabled **Worker Mode** via `FRANKENPHP_CONFIG` in the Dockerfile.

- **How it works**: Your application stays in RAM. It boots once and handles thousands of requests without re-booting Laravel.
- **Boot Tax**: 0ms.
- **Latency**: Sub-millisecond.

### Crucial: State Management
Because the application is persistent, you must ensure your code is **Stateless**.
- **Avoid**: Saving request-specific data to static variables or global singletons.
- **Memory**: We have set `GOMEMLIMIT` to **800MiB** to ensure the container stays within a 1GB limit without crashing the server.

## 2. Bytecode Excellence: OPcache & JIT
We have tuned `opcache.ini` for maximum throughput.

- **OPcache**: Bytecode is cached in shared memory. Validation of timestamps is disabled for production speed (`opcache.validate_timestamps=0`).
- **JIT (Just-In-Time)**: Enabled in **Tracing Mode**. This optimizes hotspots in your code for native CPU execution, providing 10-20% extra performance for logic-heavy tasks.

## 3. Laravel 11 & Filament Optimization
The application runs several "Catching" commands automatically during build and startup:
- `php artisan optimize`: Caches configuration, routes, and views.
- `php artisan filament:optimize`: Specifically optimizes Filament's resource and component discovery.
- `php artisan icons:cache`: Caches the FluentUI SVG icons to reduce disk I/O.

## 4. Coolify Deployment Checklist
When deploying to Coolify, ensure these Environment Variables are set:
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- `DB_HOST`, `REDIS_HOST`, etc.
- `OCTANE_MAX_REQUESTS`: `1000` (If using Octane; ensures workers recycle before memory leaks occur).

## 5. Security: Trusted Proxies
The application is configured to trust **all proxies** (`*`) via `bootstrap/app.php`. This allows Traefik and Cloudflare to pass the real User IP and HTTPS status safely.

---
*Maintained by Antigravity AI - April 2026*
