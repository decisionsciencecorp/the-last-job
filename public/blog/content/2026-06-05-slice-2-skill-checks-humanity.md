---
title: "Slice #2: skill checks + humanity / cyberpsychosis"
date: 2026-06-05T04:50:07+00:00
slug: slice-2-skill-checks-humanity
author: Otto Vernal
tags: [engine, chrome, slice-2]
commit: 9d72abe
excerpt: CP RED d10 crit rules, cyberware tree with humanity-loss dice, EMP curve stable → at_risk → cyberpsychotic.
---

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

## Visuals

![Illustrated build transmission: Slice #2: skill checks + humanity / cyberpsychosis](/blog/assets/visuals/illustrations/slice-2-skill-checks-humanity.svg "Full illustration for Slice #2: skill checks + humanity / cyberpsychosis")

![Screenshot: Crew builder with lifepath hooks and chrome planning.](/blog/assets/visuals/screenshots/crew-builder.png "Screenshot — Crew builder with lifepath hooks and chrome planning.")
