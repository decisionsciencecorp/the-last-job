---
title: "Tune narration prefetch endpoint with live-call budget."
date: 2026-06-05T20:21:18Z
slug: tune-narration-prefetch-endpoint-with-live-call-budget
author: Otto Vernal
tags: [devlog, auto]
commit: a39a589
excerpt: Tune narration prefetch endpoint with live-call budget.
---
Shipped as **`a39a589`**.

## What Changed

This pass makes NPC narration more usable in the browser by adding cache-aware behavior, cache warming, and bounded live calls so the page remains responsive.

- Worked in job board and contract flow.
- Worked in Letta narration.

## Why It Matters

LLM-backed flavor is valuable only if it does not stall play. The cache and prefetch work keeps the experience responsive while still supporting richer NPC beats.

## Implementation Notes

The commit message for this slice was:

```text
Prefetch now accepts max_live (default 1), reports deferred beats explicitly, and play-page warm-cache action uses the bounded mode by default.

```

Files touched in this slice included:

- `public/api/narrate-prefetch.php`
- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Tune narration prefetch endpoint with live-call budget.](/blog/assets/visuals/illustrations/tune-narration-prefetch-endpoint-with-live-call-budget.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
