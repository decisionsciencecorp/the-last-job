---
title: "Add Letta NPC glue: PHP client, SQLite cache, play.php narration"
date: 2026-06-05T05:37:13Z
slug: add-letta-npc-glue-php-client-sqlite-cache-play-php-narratio
author: Otto Vernal
tags: [devlog, auto]
commit: afeae36
excerpt: Add Letta NPC glue: PHP client, SQLite cache, play.php narration
---
Shipped as **`afeae36`**.

## What Changed

This pass makes NPC narration more usable in the browser by adding cache-aware behavior, cache warming, and bounded live calls so the page remains responsive.

- Worked in job board and contract flow.
- Worked in Letta narration.
- Worked in test coverage.

## Why It Matters

LLM-backed flavor is valuable only if it does not stall play. The cache and prefetch work keeps the experience responsive while still supporting richer NPC beats.

## Implementation Notes

The commit message for this slice was:

```text
- LettaClient + NpcIntentBroker + LettaResponseCache (keyed by run/beat/npc/context hash)
- play.php optional narrate=1 enriches beats with cached NPC dialogue
- config.letta.example.php; host config at DB_PARENT/config/letta.php on multihost
- 50/50 tests (cache idempotency + stable hashes)

Agent box: NPC agent on Venice proxy; nginx :8283 public proxy to Letta.
```

Files touched in this slice included:

- `.gitignore`
- `public/includes/Letta/LettaClient.php`
- `public/includes/Letta/LettaConfig.php`
- `public/includes/Letta/LettaResponseCache.php`
- `public/includes/Letta/LettaServices.php`
- `public/includes/Letta/NpcIntentBroker.php`
- `public/includes/config.letta.example.php`
- `public/play.php`
- `tests/run-tests.php`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Add Letta NPC glue: PHP client, SQLite cache, play.php narration](/blog/assets/visuals/illustrations/add-letta-npc-glue-php-client-sqlite-cache-play-php-narratio.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
