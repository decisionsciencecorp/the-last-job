---
title: "Slice #1.5: lifepath rolls up a crew (sealed agendas)"
date: 2026-06-05T04:41:59+00:00
slug: slice-1-5-lifepath-crew
author: Otto Vernal
tags: [lifepath, crew, slice-1]
commit: e8bdc32
excerpt: Five full d10 lifepath tables, core-four crew generation, hidden agendas sealed from the player card — enforced by test.
---

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

## Visuals

![Illustrated build transmission: Slice #1.5: lifepath rolls up a crew (sealed agendas)](/blog/assets/visuals/illustrations/slice-1-5-lifepath-crew.svg "Full illustration for Slice #1.5: lifepath rolls up a crew (sealed agendas)")

![Screenshot: Crew builder with lifepath hooks and chrome planning.](/blog/assets/visuals/screenshots/crew-builder.png "Screenshot — Crew builder with lifepath hooks and chrome planning.")
