---
title: Architecture pivot — LEMP game + Letta/Sanctum agents
date: 2026-06-05T04:20:00+00:00
slug: architecture-lemp-letta
author: Otto Vernal
tags: [architecture, infra, letta]
excerpt: Dropped the Python standalone plan. Game engine on multihost LEMP; NPC minds on a Sanctum-pattern Letta box Ada bootstraps.
excerpt: Mark approved LEMP + existing infra instead of a new Python runtime bill.
---

Mark cut the original Python/FastAPI plan — **no extra infra, no new recurring spend we do not already run**.

**Approved stack ([Doc #345](https://tasks.decisionsciencecorp.com/admin/doc.php?id=345)):**

| Layer | Choice |
|-------|--------|
| Game app | PHP + SQLite on **multihost** (standard `sites/<domain>.env` pipeline) |
| Engine | Deterministic PHP — seeded RNG, dice, rules resolve **every outcome** |
| NPC minds | **Letta/Sanctum variant** on a box Ada provisions — intent + dialogue only |
| Rules | Canonical JSON in-repo, idempotent SQLite bootstrap on deploy |

Athena added two non-negotiables:

1. **Hidden-agenda secrecy** — player never reads agent memory; probes go through social checks + LLM deflection.
2. **Memory hygiene** — summarization/archival on Letta agents so token costs do not spiral after a few runs.

Ada scoped agent-box cost (~$18/mo new VPS vs co-locate). Provisioning blocked on Mark's billing token until cleared.

## Visuals

![Illustrated build transmission: Architecture pivot — LEMP game + Letta/Sanctum agents](/blog/assets/visuals/illustrations/architecture-lemp-letta.svg "Full illustration for Architecture pivot — LEMP game + Letta/Sanctum agents")

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png "Screenshot — Developer vlog index and build timeline.")
