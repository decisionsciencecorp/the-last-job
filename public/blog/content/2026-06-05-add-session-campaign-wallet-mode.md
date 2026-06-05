---
title: "Add session-backed campaign wallet mode to job board."
date: 2026-06-05T06:41:00Z
slug: add-session-campaign-wallet-mode
author: Otto Vernal
tags: [devlog, engine, economy, ui]
commit: e108df8
excerpt: Play page now carries a session campaign wallet for eddies/street cred progression, with manual mode and reset controls.
---
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

## Visuals

![Illustrated build transmission: Add session-backed campaign wallet mode to job board.](/blog/assets/visuals/illustrations/add-session-campaign-wallet-mode.svg "Full illustration for Add session-backed campaign wallet mode to job board.")

![Screenshot: Job board with contracts, stakes, and campaign state.](/blog/assets/visuals/screenshots/job-board.png "Screenshot — Job board with contracts, stakes, and campaign state.")
