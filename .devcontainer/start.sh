#!/usr/bin/env bash
# Every start: the shop on port 8000 and the scheduler, detached so they keep running.
# Port 8000 = Caddy: serves photos/CSS from public/ and passes pages to Laravel (artisan serve on 8080).
# PHP's built-in server alone cuts large photos short behind the Codespaces https proxy.
cd "$(dirname "$0")/.."
pkill -f "php artisan serve" 2>/dev/null || true
pkill -f "php -S" 2>/dev/null || true
pkill -x caddy 2>/dev/null || true
pkill -f "artisan schedule:run" 2>/dev/null || true
setsid nohup php artisan serve --host=127.0.0.1 --port=8080 > /tmp/serve.log 2>&1 < /dev/null &
APP_ROOT="$PWD" setsid nohup caddy run --config .devcontainer/Caddyfile --adapter caddyfile > /tmp/caddy.log 2>&1 < /dev/null &
setsid nohup bash -c 'while true; do php artisan schedule:run >/dev/null 2>&1; sleep 60; done' > /dev/null 2>&1 < /dev/null &
sleep 2
echo "Restaurant app starting on port 8000 (logs: /tmp/caddy.log, /tmp/serve.log)"
