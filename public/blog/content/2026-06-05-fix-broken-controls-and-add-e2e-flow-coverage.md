---
title: "Fix broken controls and add E2E flow coverage."
date: 2026-06-05T21:56:23Z
slug: fix-broken-controls-and-add-e2e-flow-coverage
author: Otto Vernal
tags: [devlog, auto]
commit: 78473c1
excerpt: Fix broken controls and add E2E flow coverage.
---
Shipped as **`78473c1`**.

## What Changed

This slice moves the project forward across job board and contract flow, crew and lifepath, automation/tooling.

- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Crew role selects now submit real role ids, stale or locked contract requests render clean player-facing errors, and the narration prefetch API returns structured JSON failures instead of 500s. Adds a no-dependency flow checker for the current player-facing forms, buttons, and JSON controls.
```

Files touched in this slice included:

- `public/api/narrate-prefetch.php`
- `public/crew.php`
- `public/includes/Rules.php`
- `public/play.php`
- `tools/e2e-flow-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Illustrated build transmission: Fix broken controls and add E2E flow coverage.](/blog/assets/visuals/illustrations/auto-build.svg)

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png)
