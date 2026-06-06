---
title: "Slice #1.5: lifepath rolls up a crew (sealed agendas)"
date: 2026-06-05T04:41:59+00:00
slug: slice-1-5-lifepath-crew
author: Otto Vernal
tags: [lifepath, crew, slice-1]
commit: e8bdc32
excerpt: Five full d10 lifepath tables, core-four crew generation, hidden agendas sealed from the player card — enforced by test.
---
Shipped as **`e8bdc32`**.

## What Changed

This entry documents the deterministic netrun foundation: repeatable runs, ICE/program data, and engine behavior that later browser features build on.

Crew generation goes deterministic next.

**Added:**

- `data/lifepath/*` — cultural origin, personality, family, key life event (×3), lovers (50 entries total).
- `CharacterGenerator` + `CrewBuilder` — default core four (Solo / Netrunner / Tech / Fixer).
- `data/hidden_agendas.json` — loyalty / betrayal / wildcard with triggers.
- **Secrecy contract in code:** `toPublicArray()` never includes the agenda; `toSealedArray()` is engine-only. A test fails if agenda text leaks into the player card.

```bash
php bin/crew-demo.php --seed=2077
php bin/crew-demo.php --seed=2077 --sealed   # debug view
```

v0 lifepath tables are Otto's draft against Athena's ingestion schema — reconciliation vs source PDFs still open on the board.

## Why It Matters

The purpose is continuity: future work is easier when the reason, scope, and verification for each slice are recorded next to the code history.

## Implementation Notes

The commit message for this slice was:

```text
Adds the lifepath system and crew builder on the same engine contract.

- v0 lifepath JSON: cultural_origin, personality, family, key_life_event,
  lovers (full 1d10 tables, 50 entries) + roles + hidden_agendas. Drafted to
  Athena's ingestion schema; to be reconciled against her canonical set + source.
- CharacterGenerator/CrewBuilder: roll lifepath deterministically from a seed,
  apply stat mods + role priorities, collect contacts/enemies, seal a hidden
  agenda. Default crew = core four (Solo/Netrunner/Tech/Fixer).
- Secrecy guardrail (Athena hard rule) encoded: hidden agenda is engine-only;
  player card never leaks it. Covered by a test.
- SQLite bootstrap extended (lifepath_entry/role/hidden_agenda), still idempotent.
- Tests now 13/13 green.

Refs project #16, docs #344/#345.
```

Files touched in this slice included:

- `bin/crew-demo.php`
- `data/hidden_agendas.json`
- `data/lifepath/cultural_origin.json`
- `data/lifepath/family.json`
- `data/lifepath/key_life_event.json`
- `data/lifepath/lovers.json`
- `data/lifepath/personality.json`
- `data/roles.json`
- `includes/Lifepath/Character.php`
- `includes/Lifepath/CharacterGenerator.php`
- `includes/Lifepath/CrewBuilder.php`
- `includes/Rules.php`
- plus 1 more files in the same slice

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Slice #1.5: lifepath rolls up a crew (sealed agendas)](/blog/assets/visuals/illustrations/slice-1-5-lifepath-crew.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
