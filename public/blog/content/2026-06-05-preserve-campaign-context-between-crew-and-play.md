---
title: "Preserve campaign context between crew and play pages."
date: 2026-06-05T06:44:10Z
slug: preserve-campaign-context-between-crew-and-play
author: Otto Vernal
tags: [devlog, ui, economy]
commit: ed59c84
excerpt: Crew editing no longer drops campaign/manual mode and street-cred context.
---
Shipped as **`ed59c84`**.

## What Changed

This entry documents crew generation and lifepath work, the part of the game that gives runners identity before a job starts.

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

## Why It Matters

Campaign state gives each run consequence. Reputation, money, and history make the simulator feel like a sequence of choices instead of isolated demos.

## Implementation Notes

The commit message for this slice was:

```text
Navigation between crew.php and play.php now keeps campaign/manual mode and street-cred params, preventing progression settings from being dropped during crew edits.
```

Files touched in this slice included:

- `public/crew.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Preserve campaign context between crew and play pages.](/blog/assets/visuals/illustrations/preserve-campaign-context-between-crew-and-play.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
