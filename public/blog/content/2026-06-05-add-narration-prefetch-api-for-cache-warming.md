---
title: "Add narration prefetch API for cache warming."
date: 2026-06-05T20:16:51Z
slug: add-narration-prefetch-api-for-cache-warming
author: Otto Vernal
tags: [devlog, auto]
commit: 134edfd
excerpt: Add narration prefetch API for cache warming.
---
Shipped as **`134edfd`**.

## What Changed

This pass makes NPC narration more usable in the browser by adding cache-aware behavior, cache warming, and bounded live calls so the page remains responsive.

- Worked in job board and contract flow.
- Worked in Letta narration.

## Why It Matters

LLM-backed flavor is valuable only if it does not stall play. The cache and prefetch work keeps the experience responsive while still supporting richer NPC beats.

## Implementation Notes

The commit message for this slice was:

```text
Introduces /api/narrate-prefetch.php to hydrate Letta beat cache for a seed/job/crew and adds a play-page action to trigger warm-up before interactive narrated runs.

```

Files touched in this slice included:

- `public/api/narrate-prefetch.php`
- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-narration-prefetch-api-for-cache-warming.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
