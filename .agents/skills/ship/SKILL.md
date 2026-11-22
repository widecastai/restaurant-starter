---
name: ship
description: Safely publish the learner's save points (making a save point first from the note after $ship) to their own GitHub repository - scan for secrets and personal data first, show what goes out, push only after a yes, then print the repo link and the last 3 save points. Use when the learner types $ship or asks to put their app or code on GitHub.
---

# Ship

Public means anyone on the internet can read it. So you scan first, and you stop if anything is found.

## Steps
1. Run `git status --short`. If there are unsaved changes:
   - if the learner typed a note after `$ship` (for example `$ship Phone fixes`), make the save point first with
     their words as the note, following the save-point skill (its secret-file check, `git add -A`, `git commit`);
   - with no note, ask ONE question: "In a few words: what changed for your customers?" and use the answer.
2. Scan what this push adds. The class starter itself was checked before class; its licence and photo credits
   (`LICENSE.md`, `composer.json`, `composer.lock`, `content/SOURCES.md`, `.env.example`) are not findings.
   - Secret files anywhere in `git ls-files`: `.env`, `.admin-login.txt`, `auth.json`, `*.key`, `*.pem`, `id_rsa`.
   - In the save points not yet on GitHub (`git log origin/main..HEAD -p`, or the whole tree if there is no
     `origin/main` yet), look for secret text: `APP_KEY=base64`, `password=`, `sk-`, `ghp_`, `github_pat_`, `AKIA`,
     `-----BEGIN`; and personal data: an email that does not end in `.test`, `example.com` or
     `users.noreply.github.com`, a phone number that is not a `555` number, a street address that is not the demo one,
     a surname, a school name.
   - The save points' author email (`git log origin/main..HEAD --format=%ae`): fine if it ends in
     `users.noreply.github.com`. Otherwise it is personal data that would go public: say so, show only the part
     before the @ hidden ("your email ***@<domain>"), and tell the learner to send `Help: my save points carry my
     email` to their teacher in the teacher chat. Do not rewrite history yourself.
3. If anything was found: list each finding with its file and line, say what to do in plain
   words, and stop. Do not push. Say: "Fix these, make a save point, then type $ship again."
4. If nothing was found, print the scan line exactly like this, then the files going out
   (`git diff --stat origin/main..HEAD`, or "first push: all files"):
   ```
   Scan: no secrets, no personal data (<n> files checked)
   ```
   Ask ONE question: "Are your photos food only, with no faces of real people? If so, say yes to send these save
   points to your public GitHub repository. (yes / no)". No yes, no push.
5. After yes: `git push origin HEAD:main`. Pushing needs the network and git: request the learner's approval (they approve in the Codex panel). Never hand git commands to the learner to type.
6. Print the repo link (from `git remote get-url origin`, without `.git`) and `git log --oneline -3` in a code block.
   Say: "Copy the link, the scan line and these 3 lines to your teacher chat as proof."

## Never
- Never push if the scan found anything. Never `--force`. Never make the repository public or private, never change
  GitHub settings, never sign in or ask for a password or key.

## Check (the learner does this)
Open the repo link in a new tab: your newest save point note is shown at the top of the file list.
