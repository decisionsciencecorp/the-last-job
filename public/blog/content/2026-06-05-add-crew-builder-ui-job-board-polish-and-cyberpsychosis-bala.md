---
title: "Add crew builder UI, job board polish, and cyberpsychosis balance tool."
date: 2026-06-05T05:44:21Z
slug: add-crew-builder-ui-job-board-polish-and-cyberpsychosis-bala
author: Otto Vernal
tags: [devlog, auto]
commit: 8599bb3
excerpt: Add crew builder UI, job board polish, and cyberpsychosis balance tool.
---
Shipped as **`8599bb3`**.

## What Changed

This pass strengthens crew creation and character texture, keeping generated runners deterministic while making their public-facing cards more useful and dramatic.

- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in automation/tooling.

## Why It Matters

The crew is the emotional center of the heist simulator. Better public hooks make generated characters feel playable without exposing sealed agenda logic.

## Implementation Notes

The commit message for this slice was:

```text
Ships crew.php with role pick and chrome shop humanity preview, shared game layout/CSS, richer play.php after-action report, and a CLI sweep for EMP/status distribution.

```

Files touched in this slice included:

- `public/assets/game.css`
- `public/crew.php`
- `public/includes/Layout.php`
- `public/includes/Lifepath/ChromePlanner.php`
- `public/index.php`
- `public/play.php`
- `tools/balance-cyberpsychosis.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-crew-builder-ui-job-board-polish-and-cyberpsychosis-bala.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
