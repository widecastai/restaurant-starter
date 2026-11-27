#!/usr/bin/env bash
# Start (or restart) the shop. Safe to run any time, as often as you like:  bash .devcontainer/start.sh
# Port 8000 = Caddy: serves photos/CSS from public/ and passes pages to Laravel (artisan serve on 8080).
# PHP's built-in server alone cuts large photos short behind the Codespaces https proxy.
# A keeper loop checks both ports every 5 s and starts again whatever stopped (and runs the scheduler each minute),
# so the app comes back by itself if something stops Caddy or takes port 8000.
cd "$(dirname "$0")/.."
APP="$PWD"
# One start at a time (Codespace start, setup, a learner and Codex may all run this)
exec 9>/tmp/restaurant-start.lock; flock -w 60 9 || true
# Stop the old keeper, the scheduler loop, our servers, and anything else holding 8000 / 8080
pkill -f "restaurant-keeper" 2>/dev/null || true
pkill -f "artisan schedule:run" 2>/dev/null || true
pkill -f "^php artisan serve" 2>/dev/null || true
pkill -f "^php -S" 2>/dev/null || true
pkill -x caddy 2>/dev/null || true
fuser -k -TERM 8000/tcp 8080/tcp >/dev/null 2>&1 || true
for i in $(seq 1 20); do fuser 8000/tcp 8080/tcp >/dev/null 2>&1 || break; sleep 0.5; done
fuser -k -KILL 8000/tcp 8080/tcp >/dev/null 2>&1 || true
for f in /tmp/caddy.log /tmp/serve.log /tmp/keeper.log; do [ -f "$f" ] && [ "$(stat -c %s "$f")" -gt 1000000 ] && : > "$f"; done
echo "$(date -u +%T) start.sh" >> /tmp/keeper.log
APP_ROOT="$APP" setsid nohup bash -c '
  cd "$APP_ROOT"
  up(){ (echo > /dev/tcp/127.0.0.1/$1) >/dev/null 2>&1; }
  # who holds port 8000 (by port, not by name: a stopped caddy can stay listed as a zombie)
  holder(){ p=$(fuser 8000/tcp 2>/dev/null | awk "{print \$1}"); [ -n "$p" ] && cat /proc/$p/comm 2>/dev/null; }
  log(){ echo "$(date -u +%T) keeper: $*" >> /tmp/keeper.log; }
  t=0
  while true; do
    if ! up 8080; then
      pkill -f "^php artisan serve" 2>/dev/null; sleep 0.3
      nohup php artisan serve --host=127.0.0.1 --port=8080 >> /tmp/serve.log 2>&1 < /dev/null &
      log "started artisan serve (8080)"; sleep 2
    fi
    h=$(holder)
    if [ "$h" != caddy ]; then
      if [ -n "$h" ]; then log "$h holds port 8000, stopping it"; fuser -k -TERM 8000/tcp >/dev/null 2>&1; sleep 1; fi
      nohup caddy run --config .devcontainer/Caddyfile --adapter caddyfile >> /tmp/caddy.log 2>&1 < /dev/null &
      log "started caddy (8000)"
    fi
    [ $((t % 12)) -eq 0 ] && php artisan schedule:run >/dev/null 2>&1
    t=$((t + 1)); sleep 5
  done' restaurant-keeper > /dev/null 2>&1 < /dev/null 9>&- &
# Wait until the shop answers on 8000, then say so in plain words
for i in $(seq 1 30); do
  code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 http://127.0.0.1:8000/ || true)
  case "$code" in 2*|3*) echo "✅ Restaurant app is running on port 8000. Open it: Ports tab → 8000 → globe icon."; exit 0;; esac
  sleep 1
done
echo "❌ The app did not answer on port 8000 yet (last answer: ${code:-none}). Wait 30 seconds and refresh your app tab."
echo "   Still not working? Ask Codex: read /tmp/keeper.log, /tmp/caddy.log and /tmp/serve.log and explain the error in plain words."
