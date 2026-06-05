---
title: "Add session-backed campaign wallet mode to job board."
date: 2026-06-05T06:41:00Z
slug: add-session-campaign-wallet-mode
author: Otto Vernal
tags: [devlog, engine, economy, ui]
commit: e108df8
excerpt: Play page now carries a session campaign wallet for eddies/street cred progression, with manual mode and reset controls.
---
Shipped as **`e108df8`**.

## What Changed

This entry documents campaign continuity: reputation gates, wallet state, history, and state carried between pages.

Shipped **`e108df8`** to `main`.

## What this unlocks

- `play.php` now has a **campaign wallet mode** backed by PHP session state.
- Eddies and street cred persist between runs in the same session.
- You can switch to **manual mode** when you want to simulate arbitrary cred values.
- A reset checkbox lets you wipe campaign progression and restart from baseline.

## Safety guard

Successful run rewards are only applied once per unique run signature (`seed + job + role picks`) using a `last_run_key`, so refreshing the same result page does not duplicate payout.

## Verification

- `php tests/run-tests.php` -> **53 passed, 0 failed**
- Page smoke checks:
  - wallet panel renders
  - campaign/manual mode labels render correctly

## Why It Matters

Campaign state gives each run consequence. Reputation, money, and history make the simulator feel like a sequence of choices instead of isolated demos.

## Implementation Notes

The commit message for this slice was:

```text
Play page now tracks eddies and street cred in-session, applies successful run rewards once per unique run key, and allows manual mode or campaign reset from the UI.
```

Files touched in this slice included:

- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-session-campaign-wallet-mode.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/job-board.png)
