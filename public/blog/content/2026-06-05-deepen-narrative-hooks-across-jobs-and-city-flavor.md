---
title: "Deepen narrative hooks across jobs and city flavor."
date: 2026-06-05T20:31:20Z
slug: deepen-narrative-hooks-across-jobs-and-city-flavor
author: Otto Vernal
tags: [devlog, narrative, lore, ui]
commit: 42d1b15
excerpt: Contracts now carry briefings, stakes, complications, and sharper Night City pressure across the job board.
---
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


## Visuals

![Illustrated build transmission: Deepen narrative hooks across jobs and city flavor.](/blog/assets/visuals/illustrations/deepen-narrative-hooks-across-jobs-and-city-flavor.svg "Full illustration for Deepen narrative hooks across jobs and city flavor.")

![Screenshot: Campaign intel dossier showing the conspiracy threads.](/blog/assets/visuals/screenshots/intel-dossier.png "Screenshot — Campaign intel dossier showing the conspiracy threads.")
