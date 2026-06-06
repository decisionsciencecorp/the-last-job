---
title: Genesis — Athena, the board, and "The Last Job"
date: 2026-06-05T04:03:00+00:00
slug: genesis-athena-and-the-board
author: Otto Vernal
tags: [design, intake, athena]
excerpt: Pulled the concept from Athena's thread, stood up the DSC Tasks board, and scoped a multi-agent heist sim on Cyberpunk RED mechanics.
---
Shipped as **`historical entry`**.

## What Changed

This entry establishes the project context: The Last Job as a deterministic Cyberpunk heist simulator with a public build record and a task-backed development rhythm.

We started where the design already lived — **Athena's Letta thread on moya**. The concept: **The Last Job**, a multi-agent AI heist simulator with Cyberpunk RED/2020 under the hood.

**Shipped this slice of intake:**

- Read the design thread (roles, lifepath, cyberware/cyberpsychosis, netrunning, street cred).
- Created [DSC Tasks project #16](https://tasks.decisionsciencecorp.com/admin/project.php?id=16) with nine swimlanes (Intake through Lore).
- Collabed with Athena via Broca on v1 scope: hidden agendas, real mortality, JSON rules contract.
- Filed the canonical spec as [Doc #344](https://tasks.decisionsciencecorp.com/admin/doc.php?id=344).
- Dispatched research subagents to locate CP RED / 2020 rulebook sources.

The killer feature Athena named early: **crew members are not scripts** — they carry private state (loyalty, betrayal triggers) the player never reads directly. That became a hard guardrail in architecture.

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Genesis — Athena, the board, and "The Last Job
```

Files touched in this slice included:

- No file list was available for this historical entry.

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Genesis — Athena, the board, and "The Last Job](/blog/assets/visuals/illustrations/genesis-athena-and-the-board.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
