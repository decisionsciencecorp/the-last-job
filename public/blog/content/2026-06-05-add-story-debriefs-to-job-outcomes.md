---
title: "Add story debriefs to job outcomes."
date: 2026-06-05T20:36:00Z
slug: add-story-debriefs-to-job-outcomes
author: Otto Vernal
tags: [devlog, narrative, job-runner]
commit: d30cd41
excerpt: Job runs now close with success or failure aftermath copy, giving every outcome a story consequence.
---
Shipped as **`d30cd41`**.

## What Changed

This entry documents crew generation and lifepath work, the part of the game that gives runners identity before a job starts.

Shipped **`d30cd41`**.

## Outcome Consequences

The job runner now carries per-job `success_debrief` and `failure_debrief` text into the after-action report.

This means a completed run no longer lands as only payout, street cred, and mechanics. It now leaves a narrative scar:

- the pawnshop shard whispers Arasaka before locking itself
- the extraction target reveals living people were used to train the engram
- Militech's stolen file points toward `LAST JOB`
- Arasaka Tower can still speak with the crew's voices after a failed run

## Verification

- `php tests/run-tests.php` -> **53 passed**
- CLI smoke confirmed the `Aftermath` panel and debrief copy render on `play.php`.

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Carries per-job success and failure aftermath copy through JobRunner and renders it in the after-action report so each run lands with narrative consequence.
```

Files touched in this slice included:

- `public/data/jobs/datafort-militech.json`
- `public/data/jobs/extraction-arasaka-substation.json`
- `public/data/jobs/smashgrab-pawnshop.json`
- `public/data/jobs/the-last-job-arasaka.json`
- `public/includes/Job.php`
- `public/includes/JobRunner.php`
- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-story-debriefs-to-job-outcomes.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/intel-dossier.png)
