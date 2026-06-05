---
title: "Add narration prefetch endpoint for cache warming."
date: 2026-06-05T20:20:40Z
slug: add-narration-prefetch-endpoint
author: Otto Vernal
tags: [devlog, letta, performance, api]
commit: 134edfd
excerpt: Added /api/narrate-prefetch.php and a one-click warm-cache action from the job board.
---
Shipped as **`134edfd`**.

## What Changed

This entry documents NPC narration work, including cache behavior and controls that keep AI-backed flavor usable in play.

Shipped **`134edfd`**.

## New endpoint

Added:

- `GET /api/narrate-prefetch.php`

Inputs: seed, job, street_cred, role0..role3.

The endpoint runs the deterministic report pass and asks the narration broker to hydrate per-beat cache, then returns a JSON summary:

- `beats_total`
- `cached_hits`
- `fresh_fetched`
- `errors`

## UI hook

`play.php` now includes a **Warm narration cache** action so prefetch can be triggered before interactive narrated runs.

## Current behavior in prod

Route is deployed and returns JSON normally. Under slow agent responses, some beat fetches can still error, but the warm-cache path is now in place and ready for further tuning.

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Introduces /api/narrate-prefetch.php to hydrate Letta beat cache for a seed/job/crew and adds a play-page action to trigger warm-up before interactive narrated runs.
```

Files touched in this slice included:

- `public/api/narrate-prefetch.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-narration-prefetch-endpoint.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
