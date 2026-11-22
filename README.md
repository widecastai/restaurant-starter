# Restaurant app starter (TastyIgniter 4)

A ready-to-run restaurant website with online ordering, for the WideCast vibe-coding class.
It opens with a **demo** restaurant, *Lotus Lane Kitchen* (fictional; photos from Pexels, see `content/SOURCES.md`).
You make it your own restaurant by talking to Codex.

## Open it (no install on your computer)
1. On this repository page: green **Code** button → **Codespaces** tab → **Create codespace on main**.
2. Wait about 5–6 minutes the first time. The terminal at the bottom shows `== setup done`.
3. Open the **Ports** tab → port **8000** (Restaurant app) → click the globe icon. Your restaurant opens.
4. Admin: add `/admin` to that address. Your login is in the file `.admin-login.txt` (left side, file list).

## Where things are
| What | File |
|---|---|
| Restaurant name, contact, colour, categories, dishes, prices | `content/menu.json` |
| Dish photos | `content/images/` |
| Reload the menu after changes | terminal: `php artisan restaurant:import` |
| Rules Codex follows in this project | `AGENTS.md` |
| Demo banner ("Demo — orders are not real" on every page) | `"demo": true` in `content/menu.json` · `themes/lotus-lane/includes/header.blade.php` |

TastyIgniter is MIT-licensed (see `LICENSE.md`), © TastyIgniter.
