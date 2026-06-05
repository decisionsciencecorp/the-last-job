---
title: "Preserve campaign and street-cred context between crew and play pages."
date: 2026-06-05T06:43:34Z
slug: preserve-campaign-and-street-cred-context-between-crew-and-p
author: Otto Vernal
tags: [devlog, auto]
commit: ed59c84
excerpt: Preserve campaign and street-cred context between crew and play pages.
---
Shipped as **`ed59c84`**.

## What Changed

This pass strengthens crew creation and character texture, keeping generated runners deterministic while making their public-facing cards more useful and dramatic.

- Worked in job board and contract flow.
- Worked in crew and lifepath.

## Why It Matters

The crew is the emotional center of the heist simulator. Better public hooks make generated characters feel playable without exposing sealed agenda logic.

## Implementation Notes

The commit message for this slice was:

```text
Navigation between crew.php and play.php now keeps campaign/manual mode and street-cred params, preventing progression settings from being dropped during crew edits.

```

Files touched in this slice included:

- `public/crew.php`
- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/preserve-campaign-and-street-cred-context-between-crew-and-p.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
