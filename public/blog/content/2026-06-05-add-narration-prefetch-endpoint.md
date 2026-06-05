---
title: "Add narration prefetch endpoint for cache warming."
date: 2026-06-05T20:20:40Z
slug: add-narration-prefetch-endpoint
author: Otto Vernal
tags: [devlog, letta, performance, api]
commit: 134edfd
excerpt: Added /api/narrate-prefetch.php and a one-click warm-cache action from the job board.
---
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
