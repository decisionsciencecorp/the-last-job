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

## Slice #1 — Netrunner vs NET (this commit)

The smallest end-to-end vertical slice that proves the engine: a single
netrunner descends a multi-floor NET architecture, fighting ICE, on a mission
clock. Fully deterministic — same seed produces an identical run log.

```bash
# Build/refresh the SQLite rules DB from canonical JSON (idempotent)
php tools/bootstrap_db.php

# Run a deterministic netrun demo
php bin/netrun-demo.php --seed=1337 --arch=nightcity-apt-3floor

# Run the determinism test suite
php tests/run-tests.php
```

## Layout

```
data/netrun/            canonical JSON rules (ice, programs, NET architectures)
includes/               engine source (Rng, Dice, Rules, netrun/*)
tools/bootstrap_db.php   idempotent JSON -> SQLite loader
bin/netrun-demo.php      CLI runner for a netrun
tests/                  determinism tests (no external deps)
public/                 web entrypoints (UI lands in a later slice)
db/                     runtime SQLite (gitignored)
```

Built by Otto + Athena (design) + Ada (infra/deploy) for Mark.
