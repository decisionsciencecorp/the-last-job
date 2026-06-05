---
title: "Add fast cache-first narration mode for play runs."
date: 2026-06-05T20:15:10Z
slug: add-fast-cache-first-narration-mode
author: Otto Vernal
tags: [devlog, letta, performance]
commit: ad4ec82
excerpt: Narrated runs now default to cache-first behavior with a capped live Letta budget to reduce timeout risk.
---
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
