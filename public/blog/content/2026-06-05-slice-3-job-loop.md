---
title: "Slice #3: the job loop — crew, clock, aftermath, betrayals surface"
date: 2026-06-05T04:55:05+00:00
slug: slice-3-job-loop
author: Otto Vernal
tags: [engine, loop, agendas, slice-3]
commit: 939876d
excerpt: End-to-end heist — on-site obstacles + netrun on one mission clock, payout, street cred, hidden agendas trigger in aftermath.
---
Shipped as **`939876d`**.

## What Changed

This entry documents the deterministic netrun foundation: repeatable runs, ICE/program data, and engine behavior that later browser features build on.

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

## Why It Matters

Campaign state gives each run consequence. Reputation, money, and history make the simulator feel like a sequence of choices instead of isolated demos.

## Implementation Notes

The commit message for this slice was:

```text
Closes the core state-machine loop. A crew pulls a job, resolves on-site
obstacles via skill checks while the netrunner descends the NET, all on one
shared mission clock; the aftermath pays out, moves street cred, and surfaces
any hidden agendas whose trigger fired.

- Economy: eddies wallet + street cred with rep tiers (nobody/known/reputable/
  legend gate for The Last Job). MissionClock: shared tick budget for crew +
  netrun; expiry blows the window.
- Job model + JSON (Arasaka extraction, pawnshop smash-grab) + a 2nd NET arch.
  Obstacles pick the best-suited crew member (stat + role bonus).
- JobRunner: orchestrates crew checks + netrun on the shared clock, computes
  success (data pulled, ground held, clock intact), applies payout iff success.
- Hidden-agenda triggers (deterministic): run emits fired conditions
  (run_success/failed, big_payout, corp_job, crew_wounded, enemy_present); each
  crew member's sealed agenda surfaces only when its trigger_on fires (or
  'always'); 'never' stays sealed. The multi-agent betrayal/loyalty payoff.
- Demo bin/job-demo.php prints a full after-action report.
- Tests now 34/34 (economy, clock, jobrunner determinism, payout-iff-success,
  trigger soundness + secrecy at the report layer).

Refs project #16, doc #345.
```

Files touched in this slice included:

- `bin/job-demo.php`
- `data/hidden_agendas.json`
- `data/jobs/extraction-arasaka-substation.json`
- `data/jobs/smashgrab-pawnshop.json`
- `data/netrun/architectures/pawnshop-2floor.json`
- `includes/Economy.php`
- `includes/Job.php`
- `includes/JobRunner.php`
- `includes/MissionClock.php`
- `includes/Rules.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/slice-3-job-loop.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
