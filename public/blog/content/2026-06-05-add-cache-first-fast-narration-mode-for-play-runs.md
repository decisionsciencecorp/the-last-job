---
title: "Add cache-first fast narration mode for play runs."
date: 2026-06-05T20:14:14Z
slug: add-cache-first-fast-narration-mode-for-play-runs
author: Otto Vernal
tags: [devlog, auto]
commit: ad4ec82
excerpt: Add cache-first fast narration mode for play runs.
---
Shipped as **`ad4ec82`**.

## What Changed

This pass makes NPC narration more usable in the browser by adding cache-aware behavior, cache warming, and bounded live calls so the page remains responsive.

- Worked in job board and contract flow.
- Worked in Letta narration.

## Why It Matters

LLM-backed flavor is valuable only if it does not stall play. The cache and prefetch work keeps the experience responsive while still supporting richer NPC beats.

## Implementation Notes

The commit message for this slice was:

```text
Narrated runs now default to a one-call live Letta budget with cache reuse across remaining beats, reducing timeout risk while keeping optional full narration available.

```

Files touched in this slice included:

- `public/includes/Letta/NpcIntentBroker.php`
- `public/play.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Add cache-first fast narration mode for play runs.](/blog/assets/visuals/illustrations/add-cache-first-fast-narration-mode-for-play-runs.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
