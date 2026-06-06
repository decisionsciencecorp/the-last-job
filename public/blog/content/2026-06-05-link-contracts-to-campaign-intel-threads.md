---
title: "Link contracts to campaign intel threads."
date: 2026-06-05T20:55:00Z
slug: link-contracts-to-campaign-intel-threads
author: Otto Vernal
tags: [devlog, narrative, campaign, ui]
commit: 95de186
excerpt: Contract cards now link directly into the campaign dossier threads they advance.
---
Shipped as **`95de186`**.

## What Changed

This entry documents crew generation and lifepath work, the part of the game that gives runners identity before a job starts.

Shipped **`95de186`**.

## Contracts Point Back To The Mystery

Each job now declares the dossier threads it touches via `intel_threads`.

The job board renders those references as small intel chips, so a contract no longer exists only as payout and difficulty. It now points back to the larger mystery:

- Engram ghosts
- Militech futures
- Tower memory
- Crew pressure

The dossier cards also have stable anchors, so links can jump directly to the thread being advanced.

## Verification

- `php tests/run-tests.php` -> **56 passed**
- Playwright smoke confirmed job-board intel chips and `/intel.php#thread.tower`
- Screenshots were inspected locally and left out of the commit

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Adds per-job dossier thread references, renders them as job-board intel links, anchors dossier cards, and validates references in the test suite.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/data/jobs/datafort-militech.json`
- `public/data/jobs/extraction-arasaka-substation.json`
- `public/data/jobs/smashgrab-pawnshop.json`
- `public/data/jobs/the-last-job-arasaka.json`
- `public/includes/Job.php`
- `public/intel.php`
- `public/play.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Link contracts to campaign intel threads.](/blog/assets/visuals/illustrations/link-contracts-to-campaign-intel-threads.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
