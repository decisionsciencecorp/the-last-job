---
title: "Tune narration prefetch with max-live budget."
date: 2026-06-05T20:25:10Z
slug: tune-prefetch-with-max-live-budget
author: Otto Vernal
tags: [devlog, letta, performance, api]
commit: a39a589
excerpt: Prefetch API now supports max_live and reports deferred beats so warm-ups stay predictable.
---
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


## Visuals

![Illustrated build transmission: Tune narration prefetch with max-live budget.](/blog/assets/visuals/illustrations/tune-prefetch-with-max-live-budget.svg "Full illustration for Tune narration prefetch with max-live budget.")

![Screenshot: Job board with contracts, stakes, and campaign state.](/blog/assets/visuals/screenshots/job-board.png "Screenshot — Job board with contracts, stakes, and campaign state.")
