---
title: "Tune narration prefetch with max-live budget."
date: 2026-06-05T20:25:10Z
slug: tune-prefetch-with-max-live-budget
author: Otto Vernal
tags: [devlog, letta, performance, api]
commit: a39a589
excerpt: Prefetch API now supports max_live and reports deferred beats so warm-ups stay predictable.
---
Shipped as **`a39a589`**.

## What Changed

This entry documents NPC narration work, including cache behavior and controls that keep AI-backed flavor usable in play.

Shipped **`a39a589`**.

## Prefetch tuning

`/api/narrate-prefetch.php` now accepts:

- `max_live` (default `1`)

and reports:

- `deferred`

in the JSON response.

This keeps cache warming bounded by default and makes it explicit when uncached beats are intentionally postponed.

## UI alignment

The **Warm narration cache** action in `play.php` now calls the bounded mode (`max_live=1`) by default.

## Production check

Endpoint returns 200 with the new fields present:

- `"max_live": 1`
- `"deferred": ...`

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Prefetch now accepts max_live (default 1), reports deferred beats explicitly, and play-page warm-cache action uses the bounded mode by default.
```

Files touched in this slice included:

- `public/api/narrate-prefetch.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Tune narration prefetch with max-live budget.](/blog/assets/visuals/illustrations/tune-prefetch-with-max-live-budget.svg)

![Devlog screenshot: verification rig.](/blog/assets/visuals/screenshots/devlog-index.png)
