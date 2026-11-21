#!/usr/bin/env bash
cd "$(dirname "$0")/.."
pkill -f "artisan serve" 2>/dev/null || true
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/serve.log 2>&1 &
nohup bash -c 'while true; do php artisan schedule:run >/dev/null 2>&1; sleep 60; done' > /dev/null 2>&1 &
