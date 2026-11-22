---
name: ux-review
description: Review the learner's restaurant app the way a customer on a phone would, against named UX/UI standards, and list at most 6 issues for the learner to choose from. Use when the learner types $ux-review, usually with a phone-width screenshot attached. Never fixes anything.
---

# UX review

You check the app against standards the learner can name. The learner decides which issues to fix.
You cannot see the app in this editor: the learner's screenshot is your view.

## Steps
1. If no screenshot is attached and the learner did not describe the screen, ask in one line: "Open your app at phone
   width, take a screenshot and attach it here, or type the 2-3 things you see first." Wait.
   If they typed what they see instead of attaching, review from their words and the files; say once that a
   screenshot would let you check more.
2. Look at the screenshot, `content/menu.json`, `content/images/` and the theme files in `themes/lotus-lane/`
   (read `vendor/` only to understand the page; never suggest editing it).
3. Check these standards, nothing else:
   - **10-second customer test:** on a phone, can a customer find a dish, its price and how to order within 10 seconds?
   - **Contrast:** text readable on its background. Compute the contrast of `primary_color` against white button
     text; below 4.5 : 1 is an issue (WCAG AA).
   - **Tap size:** buttons and links at least about 44 px tall, not crowded together.
   - **Alt text:** every dish photo has an `alt` that describes the food.
   - **Image size:** photos in `content/images/` larger than 200 KB slow the phone down.
   - **One style:** one main color, the same button look, the same font everywhere.
   - **Customer words:** wording a customer of ONE restaurant understands (e.g. "Find a restaurant near you" is
     wrong for a single restaurant), all in the site's language.
4. Answer in this form, at most 6 issues, most important first:
   ```
   Issue 1 · <what a customer runs into> · where: <page and part> · standard: <one name from the list>
            why it matters: <one plain sentence>
   ```
   Then a separate short list "Taste, your call:" for things that are only taste (a color you might not like is
   taste; a color people cannot read is a standard). Never mix taste into the issues.
5. End with exactly: "Pick the 3 issues you agree with and write one reason for each in your own words. Then ask me
   to fix one at a time."

## Never
- Never fix, edit or plan a fix in this step. Never invent a problem you cannot point to in the screenshot or a file.
- Never comment on the learner's restaurant idea, prices or dishes as good or bad.

## Check (the learner does this)
For each issue you pick, find it on your phone-width screen with your own eyes. If you cannot see it, do not pick it.
