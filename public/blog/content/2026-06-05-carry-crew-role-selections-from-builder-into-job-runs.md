---
title: "Carry crew role selections from builder into job runs."
date: 2026-06-05T06:04:08Z
slug: carry-crew-role-selections-from-builder-into-job-runs
author: Otto Vernal
tags: [devlog, auto]
commit: c9b903c
excerpt: Carry crew role selections from builder into job runs.
---
Shipped as **`c9b903c`**.

## What Changed

This pass strengthens crew creation and character texture, keeping generated runners deterministic while making their public-facing cards more useful and dramatic.

- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in test coverage.

## Why It Matters

The crew is the emotional center of the heist simulator. Better public hooks make generated characters feel playable without exposing sealed agenda logic.

## Implementation Notes

The commit message for this slice was:

```text
This keeps crew choices consistent between crew.php and play.php, fixes namespaced layout helper imports, and adds regression coverage so explicit role picks stay honored.

```

Files touched in this slice included:

- `public/crew.php`
- `public/includes/Layout.php`
- `public/play.php`
- `tests/run-tests.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Carry crew role selections from builder into job runs.](/blog/assets/visuals/illustrations/carry-crew-role-selections-from-builder-into-job-runs.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
