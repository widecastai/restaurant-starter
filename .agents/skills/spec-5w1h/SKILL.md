---
name: spec-5w1h
description: Turn the learner's OWN 5W1H answers about a customer's request (Who, What, Why, When, Where, How we know it works) into a short spec and a build plan with the 3 risk signs marked. Asks for a missing answer one question at a time and never invents answers. Use when the learner types $spec-5w1h or pastes their 5W1H. Never builds before yes.
---

# Spec from 5W1H

The learner thinks; you write it up. Their answers are the spec. 5W1H is a starting point, not a form:
a W that does not fit may be skipped or merged, and a few words are enough.

## Steps
1. Read the learner's answers. Sort them by meaning into: **Who** it is for · **What** it does · **Why** they need it ·
   **When / Where** they use it · **How we know it works**. Never grade an answer for the W it was written under.
2. Missing or too vague to build (most often "How we know it works")? Ask ONE question, with 2-3 short choices and
   "or say it in your own words". Wait for the answer. Then the next missing one, if any. At most 3 questions.
   - Never fill an answer yourself. If the learner says "you decide", give the choices again and ask them to pick.
   - A W that does not matter for this request: ask "Skip it?" once.
3. Write the spec in their words, at most 8 lines:
   ```
   Spec · <feature name in the customer's words>
   Who: … · What: … · Why: … · When/Where: … · Done when: <their How line, as a check someone can click>
   ```
4. Write the plan in 3-6 short lines: which files, and where the new work goes. Prefer, in this order: `content/`
   (menu.json data), `themes/lotus-lane/` (looks only), a new folder in `extensions/` (new behaviour, so the app's
   own code stays untouched). Never `vendor/`.
5. Mark the 3 risk signs, every time, each yes or no with what:
   ```
   Risk signs
   1. Database or deleting something: no / yes - <what>
   2. Outside the safe folders (content/, themes/lotus-lane/, extensions/): no / yes - <which files>
   3. Bigger than the customer asked, or a guess at something you did not say: no / yes - <what>
   ```
6. End with exactly: "Read the 3 risk signs. Say yes to build, or tell me what to change. Make a save point first:
   type $save-point."
7. Build nothing in this step. When the learner says yes later, build one small change at a time and say what to
   click to check it.

## Never
- Never invent the learner's answers, the customer's wishes or extra features. Never start building before "yes".
- Never ask for personal details about the customer or the learner.

## Check (the learner does this)
Read your spec: every line is something you said. If a line has an idea you never gave, tell Codex to remove it.
