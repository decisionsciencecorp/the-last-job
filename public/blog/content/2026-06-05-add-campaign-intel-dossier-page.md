---
title: "Add campaign intel dossier page."
date: 2026-06-05T20:48:00Z
slug: add-campaign-intel-dossier-page
author: Otto Vernal
tags: [devlog, narrative, campaign, ui]
commit: 5e546e6
excerpt: The game now has a data-driven intel dossier that ties the Arasaka, Militech, Tower, and crew-pressure threads together.
---
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


## Visuals

![Illustrated build transmission: Add campaign intel dossier page.](/blog/assets/visuals/illustrations/add-campaign-intel-dossier-page.svg "Full illustration for Add campaign intel dossier page.")

![Screenshot: Campaign intel dossier showing the conspiracy threads.](/blog/assets/visuals/screenshots/intel-dossier.png "Screenshot — Campaign intel dossier showing the conspiracy threads.")
