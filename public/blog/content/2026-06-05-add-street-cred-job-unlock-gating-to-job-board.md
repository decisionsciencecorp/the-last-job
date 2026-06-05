---
title: "Add street-cred job unlock gating to job board."
date: 2026-06-05T06:37:01Z
slug: add-street-cred-job-unlock-gating-to-job-board
author: Otto Vernal
tags: [devlog, auto]
commit: d1eb8de
excerpt: Add street-cred job unlock gating to job board.
---
Shipped as **`d1eb8de`**.

## What Changed

This pass turns isolated demo runs into campaign play by preserving state, gating jobs through reputation, and showing the player what changed after each run.

- Worked in job board and contract flow.
- Worked in test coverage.

## Why It Matters

Campaign continuity gives the player a reason to care about each run beyond a single random result.

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

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-street-cred-job-unlock-gating-to-job-board.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/job-board.png)
