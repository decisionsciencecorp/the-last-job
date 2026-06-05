---
title: "Deepen narrative hooks across jobs and city flavor."
date: 2026-06-05T20:31:20Z
slug: deepen-narrative-hooks-across-jobs-and-city-flavor
author: Otto Vernal
tags: [devlog, narrative, lore, ui]
commit: 42d1b15
excerpt: Contracts now carry briefings, stakes, complications, and sharper Night City pressure across the job board.
---
Shipped as **`42d1b15`**.

## What Changed

This entry documents story and flavor work that turns mechanics into a coherent Night City heist narrative.

Shipped **`42d1b15`**.

## Narrative pass

This was a story-density pass, not a mechanics pass.

Every contract now has:

- `briefing`
- `stakes`
- `complication`

Those fields render directly on the job board so each run has a reason to exist before the dice start moving.

## Tone upgrades

Expanded:

- fixer quotes
- news ticker items
- district ambiance
- hidden agendas

The connective tissue is now more specific: borrowed faces, dead voices, engram evidence, predictive casualty lists, and the Tower remembering people who tried to beat it before.

## Verification

- `php tests/run-tests.php` -> **53 passed**
- Job board smoke confirmed `Stakes`, `Complication`, and endgame copy render correctly.

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Adds briefing, stakes, and complication copy to contracts, surfaces that story texture on the job board, and expands fixer, news, ambiance, and hidden-agenda tables with stronger endgame pressure.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/data/flavor/district_ambiance.json`
- `public/data/flavor/fixer_quotes.json`
- `public/data/flavor/news_ticker.json`
- `public/data/hidden_agendas.json`
- `public/data/jobs/datafort-militech.json`
- `public/data/jobs/extraction-arasaka-substation.json`
- `public/data/jobs/smashgrab-pawnshop.json`
- `public/data/jobs/the-last-job-arasaka.json`
- `public/includes/Job.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/deepen-narrative-hooks-across-jobs-and-city-flavor.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/intel-dossier.png)
