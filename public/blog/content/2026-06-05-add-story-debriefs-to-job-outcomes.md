---
title: "Add story debriefs to job outcomes."
date: 2026-06-05T20:36:00Z
slug: add-story-debriefs-to-job-outcomes
author: Otto Vernal
tags: [devlog, narrative, job-runner]
commit: d30cd41
excerpt: Job runs now close with success or failure aftermath copy, giving every outcome a story consequence.
---
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
