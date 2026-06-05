---
title: "Add session run-history ledger to campaign view."
date: 2026-06-05T19:46:08Z
slug: add-session-run-history-ledger-to-campaign-view
author: Otto Vernal
tags: [devlog, auto]
commit: b574f6a
excerpt: Add session run-history ledger to campaign view.
---
Shipped as **`b574f6a`**.

## What Changed

This pass turns isolated demo runs into campaign play by preserving state, gating jobs through reputation, and showing the player what changed after each run.

- Worked in job board and contract flow.

## Why It Matters

Campaign continuity gives the player a reason to care about each run beyond a single random result.

## Implementation Notes

The commit message for this slice was:

```text
play.php now records recent run outcomes in session storage and renders a compact history panel so progression has visible audit context during a play session.

```

Files touched in this slice included:

- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-session-run-history-ledger-to-campaign-view.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/run-aftermath.png)
