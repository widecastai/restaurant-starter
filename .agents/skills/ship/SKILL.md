---
name: ship
description: Safely publish the learner's save points to their own GitHub repository - scan for secrets and personal data first, show what goes out, push only after a yes, then print the repo link and the last 3 save points. Use when the learner types $ship or asks to put their app or code on GitHub.
---

# Ship

Public means anyone on the internet can read it. So you scan first, and you stop if anything is found.

## Steps
1. Run `git status --short`. If there are unsaved changes, say: "Make a save point first: type $save-point." and stop.
2. Scan everything that will be public: the files in `git ls-files` and every save point not yet on GitHub
   (`git log origin/main..HEAD -p`, or the whole tree if there is no `origin/main` yet). Look for:
   - secret files: `.env`, `.admin-login.txt`, `auth.json`, `*.key`, `*.pem`, `id_rsa`;
   - secret text: `APP_KEY=base64`, `password=`, `sk-`, `ghp_`, `github_pat_`, `AKIA`, `-----BEGIN`;
   - personal data: an email that does not end in `.test` or `example.com`, a phone number that is not a `555`
     number, a street address that is not the demo one, a surname, a school name.
3. Ask the learner ONE question: "Are all your photos of food only, with no faces of real people? (yes / no)"
4. If anything was found (or the answer is no): list each finding with its file and line, say what to do in plain
   words, and stop. Do not push. Say: "Fix these, make a save point, then type $ship again."
5. If nothing was found, print the scan line exactly like this, then the files going out
   (`git diff --stat origin/main..HEAD`, or "first push: all files"):
   ```
   Scan: no secrets, no personal data (<n> files checked)
   ```
   Ask: "Send these save points to your public GitHub repository? (yes / no)". No yes, no push.
6. After yes: `git push origin HEAD:main`. Pushing needs the network and git: request the learner's approval (they approve in the Codex panel). Never hand git commands to the learner to type.
7. Print the repo link (from `git remote get-url origin`, without `.git`) and `git log --oneline -3` in a code block.
   Say: "Copy the link, the scan line and these 3 lines to your teacher chat as proof."

## Never
- Never push if the scan found anything. Never `--force`. Never make the repository public or private, never change
  GitHub settings, never sign in or ask for a password or key.

## Check (the learner does this)
Open the repo link in a new tab: your newest save point note is shown at the top of the file list.
