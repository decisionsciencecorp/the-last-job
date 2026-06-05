# The Last Job

Multi-agent AI heist game built on Cyberpunk RED/2020 TTRPG mechanics. The
deterministic rules engine lives here (PHP + SQLite, LEMP-deployable); NPC
"minds" (crew + netrunner) are supplied by a separate Letta/Sanctum agent box.

- **Design spec:** DSC Tasks doc #344
- **Architecture:** DSC Tasks doc #345
- **Board:** DSC Tasks project #16 (The Last Job)

## Architecture (short version)

- **Engine = PHP, deterministic.** Seeded RNG; dice + rules decide every
  outcome. The LLM (Letta agent) decides NPC *intent* and *dialogue* only —
  never outcomes.
- **Rules = canonical JSON** in `data/` (Athena's ingestion schema), loaded
  into SQLite at bootstrap for fast queries.
- **Deploy = LEMP/multihost** via the standard `sites/<domain>.env` + `sync.sh`
  + cron pipeline. App code is app-level PHP only (no `.htaccess`).

## What's built (deterministic engine)

Every slice is fully deterministic — a given seed reproduces an identical
result — and covered by the dependency-free test suite (`php tests/run-tests.php`,
currently **34/34**).

- **Slice #1 — Netrunner vs NET.** A netrunner descends a multi-floor NET
  architecture, fighting/sneaking past ICE on a security clock; flatline =
  meat death; vault extraction pays out.
- **Slice #1.5 — Lifepath → crew.** Full 1d10 lifepath tables roll up a crew
  (core four: Solo/Netrunner/Tech/Fixer) with stats, contacts, enemies, and a
  **sealed hidden agenda** the player card can never leak.
- **Slice #2 — Skill checks + humanity.** CP RED 1d10 + STAT+SKILL vs DV with
  crit rules; cyberware tree with humanity-loss dice and the
  stable → at_risk → cyberpsychotic curve.
- **Slice #3 — Job loop.** Crew resolves on-site obstacles while the netrunner
  cracks the NET on a **shared mission clock**; aftermath pays eddies + street
  cred and **surfaces any hidden agendas whose trigger fired** (loyalty/betrayal).

```bash
# Build/refresh the SQLite rules DB from canonical JSON (idempotent)
php tools/bootstrap_db.php

# Demos (all deterministic)
php bin/netrun-demo.php --seed=1337                 # netrun
php bin/crew-demo.php   --seed=2077 [--sealed]      # lifepath -> crew
php bin/chrome-demo.php --seed=1337 --emp=8         # humanity / cyberpsychosis
php bin/job-demo.php    --seed=2077 --job=job.arasaka-substation  # full heist

# Test suite (no external deps)
php tests/run-tests.php
```

NPC *intent/dialogue* (the Letta agent layer) plugs in on top of these
deterministic outcomes once the agent box is provisioned (#637/#633).

## Layout

```
data/netrun/            ICE, programs, NET architectures
data/lifepath/          5 lifepath tables (cultural_origin, personality, ...)
data/cyberware.json     8-category cyberware tree (humanity-loss dice)
data/roles.json         core-four role definitions
data/hidden_agendas.json sealed agendas + machine-readable trigger_on
data/jobs/              job/contract definitions
includes/               engine (Rng, Dice, Rules, SkillCheck, Humanity,
                          Economy, MissionClock, Job, JobRunner, Lifepath/*, Netrun/*)
tools/bootstrap_db.php   idempotent JSON -> SQLite loader
bin/                    CLI demos
tests/                  determinism + secrecy tests
public/                 web entrypoints (UI lands in a later slice)
db/                     runtime SQLite (gitignored)
```

Built by Otto + Athena (design) + Ada (infra/deploy) for Mark.
