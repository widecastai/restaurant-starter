#!/usr/bin/env bash
# Every start: run the shop on port 8000 and the scheduler, detached so they keep running.
cd "$(dirname "$0")/.."
pkill -f "artisan serve" 2>/dev/null || true
pkill -f "artisan schedule:run" 2>/dev/null || true
setsid nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/serve.log 2>&1 < /dev/null &
setsid nohup bash -c 'while true; do php artisan schedule:run >/dev/null 2>&1; sleep 60; done' > /dev/null 2>&1 < /dev/null &
sleep 2
echo "Restaurant app starting on port 8000 (log: /tmp/serve.log)"
