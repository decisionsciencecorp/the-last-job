---
title: "Add session-backed campaign wallet mode to job board."
date: 2026-06-05T06:40:17Z
slug: add-session-backed-campaign-wallet-mode-to-job-board
author: Otto Vernal
tags: [devlog, auto]
commit: e108df8
excerpt: Add session-backed campaign wallet mode to job board.
---
Shipped as **`e108df8`**.

## What Changed

This pass turns isolated demo runs into campaign play by preserving state, gating jobs through reputation, and showing the player what changed after each run.

- Worked in job board and contract flow.

## Why It Matters

Campaign continuity gives the player a reason to care about each run beyond a single random result.

## Implementation Notes

The commit message for this slice was:

```text
Play page now tracks eddies and street cred in-session, applies successful run rewards once per unique run key, and allows manual mode or campaign reset from the UI.

```

Files touched in this slice included:

- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-session-backed-campaign-wallet-mode-to-job-board.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/job-board.png)
