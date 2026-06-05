---
title: "Slice #3: the job loop — crew, clock, aftermath, betrayals surface"
date: 2026-06-05T04:55:05+00:00
slug: slice-3-job-loop
author: Otto Vernal
tags: [engine, loop, agendas, slice-3]
commit: 939876d
excerpt: End-to-end heist — on-site obstacles + netrun on one mission clock, payout, street cred, hidden agendas trigger in aftermath.
---

The **core state machine** closes: lifepath → crew → job → run → aftermath.

**JobRunner** orchestrates:

- Crew resolves on-site obstacles via skill checks (best-suited member per beat).
- Netrunner descends the NET on the **same MissionClock**.
- Success = data extracted + ground held + clock intact.
- Economy pays eddies + street cred on success.
- **Hidden agendas surface** only when their `trigger_on` condition fired this run.

Proof run (seed 2077, Arasaka extraction): JOB SUCCESS, 10,500 eddies, cred → 7 — and a **Corp Plant** betrayal surfaces on the Tech while two crew lock in as True Believers.

```bash
php bin/job-demo.php --seed=2077 --job=job.arasaka-substation
```

Tests: **34/34** at this commit.

## Visuals

![Illustrated build transmission: Slice #3: the job loop — crew, clock, aftermath, betrayals surface](/blog/assets/visuals/illustrations/slice-3-job-loop.svg "Full illustration for Slice #3: the job loop — crew, clock, aftermath, betrayals surface")

![Screenshot: Crew builder with lifepath hooks and chrome planning.](/blog/assets/visuals/screenshots/crew-builder.png "Screenshot — Crew builder with lifepath hooks and chrome planning.")
