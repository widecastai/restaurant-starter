---
name: save-point
description: Make a save point (a git commit) of the learner's restaurant app, list the save points, or go back to one. Use when the learner types $save-point, or asks to save, to see their save points, or to go back / undo to an earlier save point or a class rescue point.
---

# Save point

A save point is a snapshot of the whole app with a one-line note. The learner can always come back to it.
You do the git work; the learner chooses the note and says yes. Plain words only: say "save point", not "commit".

## 1. Save (what `$save-point` means with no other words)
1. Run `git status --short`. If nothing changed, say: "Nothing new since your last save point." and stop.
2. If `.env`, `.admin-login.txt`, `auth.json`, a `.key` or a `.pem` file shows up, stop: say it is a secret that
   must stay out, and offer to add it to `.gitignore` (wait for yes).
3. If the learner gave no note, ask ONE question: "In a few words: what changed for your customers?"
   Use their words as the note (fix only spelling). Never invent the note.
4. `git add -A` then `git commit -m "<note>"`.
5. Show `git log --oneline -2` in a code block, then say:
   "Saved. Copy these 2 lines to your teacher chat as proof."

## 2. List
Show `git log --oneline -10` in a code block. Say: newest on top; each line is one save point.

## 3. Go back to a save point
1. If the learner did not say which one, show the list and ask which one (their note words are enough).
2. Say in plain words what will be lost: unsaved changes (`git status --short`) and the save points made after it
   (`git log --oneline <target>..HEAD`). Wait for "yes". No yes, no change.
3. Go back without deleting history:
   `git restore --source=<target> --staged --worktree -- .` then `git clean -fd -- .` (never `-x`: it would delete
   `.env` and `.admin-login.txt`), then `git commit -m "Back to: <note of target>"`.
4. If anything in `content/` changed, run `php artisan restaurant:import` so the app shows it.
5. Say: "Back at '<note>'. Refresh your app tab and check it."

## 4. Class rescue point (only if the learner asks for one, e.g. "rescue point part 2")
Class rescue points are tags in the class repository: `rescue-part-1`, `rescue-part-2`, `rescue-part-3`.
Class repository: https://github.com/widecastai/restaurant-starter-test
1. Prefer the learner's own save points; offer this only when they have none that works.
2. `git fetch <class repository> refs/tags/<tag>`.
3. Say what changes: the app's code goes back to the class's end of that part; the `content/` folder (their name,
   color, dishes, photos) stays theirs. Wait for "yes".
4. `git restore --source=FETCH_HEAD --staged --worktree -- . ':!content'`, then
   `git commit -m "Rescue: <tag>"`, then `php artisan restaurant:import`.

## Never
- Saving and going back write to git, which Codex may only do with the learner's approval: request it (the
  learner approves in the Codex panel). Never hand git commands to the learner to type. If it still cannot
  run, say so in one line and tell them to send `Help:` to their teacher in the teacher chat.
- Never push (that is `$ship`). Never `git reset --hard`, `git push --force` or delete save points.
- Never go back or rescue without a "yes".

## Check (the learner does this)
Open **Source Control** on the left (or ask "$save-point list"): your note is the newest line.
