---
title: "Slice #1: deterministic netrunner-vs-NET engine"
date: 2026-06-05T04:30:45+00:00
slug: slice-1-netrun-engine
author: Otto Vernal
tags: [engine, netrun, slice-1]
commit: 15aa87a
excerpt: Park-Miller RNG, ICE combat, security clock, vault extraction — same seed, identical run log. 8/8 determinism tests.
---
Shipped as **`15aa87a`**.

## What Changed

This entry explains the architecture choice: a PHP/SQLite game surface on the multihost stack with Letta-backed NPC minds kept outside the request path unless explicitly called.

First vertical slice: **one netrunner descends a NET architecture** while a mission clock ticks.

**Mechanics:**

- Custom **Park-Miller LCG** — reproducible across PHP versions (no `rand()` surprises).
- JSON rules: ICE roster, programs, prebuilt 3-floor architecture.
- Stealth bypass, ICE combat, alarm/heat, black-ICE flatline = meat death.
- Structured run log for replay/audit.

**Verify:**

```bash
php tests/run-tests.php   # 8/8 at this commit
php bin/netrun-demo.php --seed=1337
```

Repo public: [github.com/decisionsciencecorp/the-last-job](https://github.com/decisionsciencecorp/the-last-job)

The LLM netrunner "mind" layers on later — this slice proves **outcomes are 100% engine-owned**.

## Why It Matters

The purpose is continuity: future work is easier when the reason, scope, and verification for each slice are recorded next to the code history.

## Implementation Notes

The commit message for this slice was:

```text
The Last Job game engine, LEMP-deployable. Seeded RNG (Park-Miller) so a run
replays byte-identically; dice + rules decide all outcomes (the LLM/Letta layer
will only supply NPC intent, never outcomes).

- Canonical JSON rules in data/netrun (ICE roster, programs, a prebuilt
  3-floor NET architecture) per Athena's ingestion schema.
- NetrunEngine: floor descent, stealth bypass, ICE combat, alarm/heat clock,
  black-ice lethality, vault extraction; structured run log.
- Idempotent SQLite bootstrap (JSON -> tables, safe to re-run on deploy).
- Dependency-free determinism test suite (8 checks, all green).

Refs DSC Tasks project #16, design doc #344, architecture doc #345.
```

Files touched in this slice included:

- `.gitignore`
- `README.md`
- `bin/netrun-demo.php`
- `data/netrun/architectures/nightcity-apt-3floor.json`
- `data/netrun/ice.json`
- `data/netrun/programs.json`
- `includes/Dice.php`
- `includes/Netrun/NetrunEngine.php`
- `includes/Netrun/Netrunner.php`
- `includes/Rng.php`
- `includes/Rules.php`
- `includes/autoload.php`
- plus 3 more files in the same slice

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Slice #1: deterministic netrunner-vs-NET engine](/blog/assets/visuals/illustrations/slice-1-netrun-engine.svg)

![Devlog screenshot: campaign dossier.](/blog/assets/visuals/screenshots/intel-dossier.png)
