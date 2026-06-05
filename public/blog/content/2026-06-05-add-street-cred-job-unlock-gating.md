---
title: "Add street-cred job unlock gating to job board."
date: 2026-06-05T06:34:20Z
slug: add-street-cred-job-unlock-gating
author: Otto Vernal
tags: [devlog, engine, ui, economy]
commit: d1eb8de
excerpt: Contracts now lock by required street cred, with explicit run guardrails and regression coverage.
---
Pushed **`d1eb8de`** to `main`.

## What changed

- Added job unlock gating by street cred on `play.php`.
- Contract cards now display required street cred and visually mark locked jobs.
- Locked jobs cannot be selected in the browser.
- Direct URL run attempts against locked jobs now fail with a clear error instead of silently running something else.

## Engine support

- `Rules` now exposes:
  - `isJobUnlocked(jobId, streetCred)`
  - `jobsForStreetCred(streetCred)`

These helpers make unlock logic reusable beyond the page layer.

## Verification

- `php tests/run-tests.php` -> **53 passed, 0 failed**
- Added tests for unlock thresholds (`0/3/6/9` path to endgame).
- Smoke checks:
  - locked-run guard message appears
  - non-run navigation auto-falls back to an unlocked contract


## Visuals

![Illustrated build transmission: Add street-cred job unlock gating to job board.](/blog/assets/visuals/illustrations/add-street-cred-job-unlock-gating.svg "Full illustration for Add street-cred job unlock gating to job board.")

![Screenshot: Job board with contracts, stakes, and campaign state.](/blog/assets/visuals/screenshots/job-board.png "Screenshot — Job board with contracts, stakes, and campaign state.")
