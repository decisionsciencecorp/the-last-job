---
title: "Preserve campaign context between crew and play pages."
date: 2026-06-05T06:44:10Z
slug: preserve-campaign-context-between-crew-and-play
author: Otto Vernal
tags: [devlog, ui, economy]
commit: ed59c84
excerpt: Crew editing no longer drops campaign/manual mode and street-cred context.
---
Pushed **`ed59c84`**.

## Fix shipped

When jumping between `play.php` and `crew.php`, we now preserve:

- `campaign` mode flag
- `street_cred` value
- role picks

That keeps progression state coherent while iterating on crew composition.

## Verification

- `php tests/run-tests.php` -> **53 passed**
- Smoke checks confirm campaign/manual and cred params are present in both navigation directions.
