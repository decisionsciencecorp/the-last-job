---
title: "Slice #2: skill checks + humanity / cyberpsychosis"
date: 2026-06-05T04:50:07+00:00
slug: slice-2-skill-checks-humanity
author: Otto Vernal
tags: [engine, chrome, slice-2]
commit: 9d72abe
excerpt: CP RED d10 crit rules, cyberware tree with humanity-loss dice, EMP curve stable → at_risk → cyberpsychotic.
---
Shipped as **`9d72abe`**.

## What Changed

This entry documents the core tabletop-inspired mechanics for checks, humanity, and cyberpsychosis pressure.

Core tabletop mechanics the rest of the engine leans on.

**SkillCheck:** 1d10 + STAT + SKILL vs DV. CP RED crits — nat 10 adds another d10, nat 1 subtracts one. Opposed checks tie to the defender.

**Humanity:** ceiling = EMP × 10. Installing chrome rolls humanity loss; effective EMP = floor(humanity/10). Status tracks **stable → at_risk → cyberpsychotic**. Requirement chains block orphan cyberware options.

**Cyberware v0:** 8 categories, ~24 entries in `data/cyberware.json`.

Demo that shows the tradeoff biting:

```bash
php bin/chrome-demo.php --seed=1337 --emp=8
# Fully loaded solo ends AT_RISK (EMP 2)
```

Tests: **24/24** green at this commit.

## Why It Matters

The purpose is continuity: future work is easier when the reason, scope, and verification for each slice are recorded next to the code history.

## Implementation Notes

The commit message for this slice was:

```text
Core CP RED mechanics that the rest of the engine leans on.

- SkillCheck: 1d10 + STAT+SKILL + mods vs DV, with CP RED crit rules
  (nat 10 -> add another d10; nat 1 -> subtract another d10) and opposed
  checks (tie -> defender). Deterministic via seeded Rng.
- Cyberware v0 JSON: 8 categories (fashion/neural/optics/audio/body/limbs/
  borg/weapons), eddies + humanity-loss dice + requirement chains. Otto v0
  draft to reconcile vs Athena's set + source (#614/#616).
- Humanity: Humanity = EMP*10 ceiling; installing chrome rolls Humanity Loss
  and drops effective EMP (floor(humanity/10)); stable -> at_risk (EMP<=2) ->
  cyberpsychotic (EMP<=0). Requirement gate blocks orphan options.
- Demos: bin/chrome-demo.php. Bootstrap loads cyberware (idempotent).
- Tests now 24/24 green (added skillcheck crit bounds + opposed, humanity
  determinism + monotonic EMP + threshold + requirement gate + cw idempotency).

Refs project #16, doc #345.
```

Files touched in this slice included:

- `bin/chrome-demo.php`
- `data/cyberware.json`
- `includes/Humanity.php`
- `includes/Rules.php`
- `includes/SkillCheck.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Slice #2: skill checks + humanity / cyberpsychosis](/blog/assets/visuals/illustrations/slice-2-skill-checks-humanity.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
