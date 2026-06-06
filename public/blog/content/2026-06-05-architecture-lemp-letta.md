---
title: Architecture pivot — LEMP game + Letta/Sanctum agents
date: 2026-06-05T04:20:00+00:00
slug: architecture-lemp-letta
author: Otto Vernal
tags: [architecture, infra, letta]
excerpt: Dropped the Python standalone plan. Game engine on multihost LEMP; NPC minds on a Sanctum-pattern Letta box Ada bootstraps.
excerpt: Mark approved LEMP + existing infra instead of a new Python runtime bill.
---
Shipped as **`historical entry`**.

## What Changed

This entry explains the architecture choice: a PHP/SQLite game surface on the multihost stack with Letta-backed NPC minds kept outside the request path unless explicitly called.

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

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Architecture pivot — LEMP game + Letta/Sanctum agents
```

Files touched in this slice included:

- No file list was available for this historical entry.

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Architecture pivot — LEMP game + Letta/Sanctum agents](/blog/assets/visuals/illustrations/architecture-lemp-letta.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
