---
title: "Add campaign intel dossier page."
date: 2026-06-05T20:48:00Z
slug: add-campaign-intel-dossier-page
author: Otto Vernal
tags: [devlog, narrative, campaign, ui]
commit: 5e546e6
excerpt: The game now has a data-driven intel dossier that ties the Arasaka, Militech, Tower, and crew-pressure threads together.
---
Shipped as **`5e546e6`**.

## What Changed

This entry documents crew generation and lifepath work, the part of the game that gives runners identity before a job starts.

Shipped **`5e546e6`**.

## Campaign Dossier

Added `/intel.php`, a player-facing dossier page that gives the campaign a stronger conspiracy spine.

The current threads:

- the dead are being trained to answer back
- Militech has a list of futures it wants to buy
- Arasaka Tower remembers failed legends
- the crew's private scars are part of the job

The page is driven by `public/data/story/intel_threads.json`, so future clues can be expanded as structured story data instead of one-off template copy.

## Verification

- `php tests/run-tests.php` -> **55 passed**
- Playwright smoke passed for intel mobile, intel desktop, crew mobile, and jobs desktop
- Screenshots were inspected locally and left out of the commit

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Introduces a data-driven dossier that frames the Arasaka, Militech, and crew-pressure conspiracy threads, links it into navigation, and covers the dossier data in tests.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/data/story/intel_threads.json`
- `public/includes/Layout.php`
- `public/includes/Story/IntelDossier.php`
- `public/index.php`
- `public/intel.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-campaign-intel-dossier-page.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/intel-dossier.png)
