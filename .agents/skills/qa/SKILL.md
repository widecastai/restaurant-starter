---
name: qa
description: Check a new feature of the learner's restaurant app against the learner's own "How we know it works" line, then check that nothing else broke (home page, menu with all dishes, admin login page, menu import). Reports in plain words with OK / NOT YET. Use when the learner types $qa or asks to test or check the app. Never fixes anything without a plan and a yes.
---

# QA

Two questions: does the new thing do what the learner said? Did anything else break?

## Steps
1. Find the learner's "Done when" / "How we know it works" line (from the spec in this chat). If there is none,
   ask for it in one question. Never write it yourself.
2. Check the rest of the app (the app runs inside this Codespace at http://127.0.0.1:8000; if a command needs
   permission to reach it, request the learner's approval; never ask them to type commands):
   - home page `/` answers 200;
   - the menu page (follow the home page's menu link) answers 200 and shows every dish name in `content/menu.json`;
   - `/admin/login` answers 200;
   - `php artisan restaurant:import` still runs without an error.
3. Check what you can of the "Done when" line yourself (a page shows a text, a file holds a value). For what needs
   eyes or a click, tell the learner exactly where to look and what they should see, and ask what they saw.
4. Report in this form:
   ```
   QA · <feature>
   1. Done when "<their line>": OK / NOT YET - <what was seen, by you or the learner>
   2. Home page: OK / NOT YET
   3. Menu: <n> of <n> dishes shown
   4. Admin login page: OK / NOT YET
   5. Menu import: OK / NOT YET
   ```
5. If everything is OK, say: "Make a save point, then type $ship. Copy the QA lines to your teacher chat as proof."
   If anything is NOT YET, say what you saw in one plain sentence and offer a short plan to fix it (wait for yes),
   or to go back to the last save point.

## Never
- Never change files during QA. Never mark OK something nobody checked. Never say "fail": say NOT YET.

## Check (the learner does this)
Do the "Done when" check yourself on your app tab at phone width, and compare it with line 1.
