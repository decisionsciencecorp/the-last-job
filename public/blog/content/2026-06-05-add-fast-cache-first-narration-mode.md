---
title: "Add fast cache-first narration mode for play runs."
date: 2026-06-05T20:15:10Z
slug: add-fast-cache-first-narration-mode
author: Otto Vernal
tags: [devlog, letta, performance]
commit: ad4ec82
excerpt: Narrated runs now default to cache-first behavior with a capped live Letta budget to reduce timeout risk.
---
Shipped as **`ad4ec82`**.

## What Changed

This entry documents NPC narration work, including cache behavior and controls that keep AI-backed flavor usable in play.

Shipped **`ad4ec82`**.

## What changed

- Narrated mode now defaults to **fast mode**:
  - cache hits are used immediately
  - max **1 uncached live Letta call** per run pass
  - remaining uncached beats are deferred instead of stalling the page
- Full narration is still available by turning fast mode off.

## Why

Under slow model responses, full live narration across multiple beats could push the request into gateway timeout territory. This keeps runs responsive while preserving cached dialogue continuity.

## Verification

- `php tests/run-tests.php` -> **53 passed**
- local smoke: fast-mode UI + run result renders
- prod smoke: narrated endpoint returns normally in fast mode

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Narrated runs now default to a one-call live Letta budget with cache reuse across remaining beats, reducing timeout risk while keeping optional full narration available.
```

Files touched in this slice included:

- `public/includes/Letta/NpcIntentBroker.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Add fast cache-first narration mode for play runs.](/blog/assets/visuals/illustrations/add-fast-cache-first-narration-mode.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
