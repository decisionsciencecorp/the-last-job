---
title: "Add street-cred job unlock gating to job board."
date: 2026-06-05T06:34:20Z
slug: add-street-cred-job-unlock-gating
author: Otto Vernal
tags: [devlog, engine, ui, economy]
commit: d1eb8de
excerpt: Contracts now lock by required street cred, with explicit run guardrails and regression coverage.
---
Shipped as **`d1eb8de`**.

## What Changed

This entry documents campaign continuity: reputation gates, wallet state, history, and state carried between pages.

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

## Why It Matters

Campaign state gives each run consequence. Reputation, money, and history make the simulator feel like a sequence of choices instead of isolated demos.

## Implementation Notes

The commit message for this slice was:

```text
Play UI now shows locked contracts by required cred, blocks invalid run attempts, and adds rules/test coverage for unlock filtering up to endgame.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/includes/Rules.php`
- `public/play.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-street-cred-job-unlock-gating.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/job-board.png)
