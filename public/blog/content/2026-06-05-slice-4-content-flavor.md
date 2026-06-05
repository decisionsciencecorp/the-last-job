---
title: "Slice #4: deeper NET content + Night City flavor"
date: 2026-06-05T05:01:06+00:00
slug: slice-4-content-flavor
author: Otto Vernal
tags: [content, netrun, flavor, slice-4]
commit: ae20061
excerpt: ICE roster to 14 (Kraken, Dragon), 10 programs, Militech + Arasaka endgame jobs, fixer quotes and news ticker.
---
Shipped as **`ae20061`**.

## What Changed

This entry explains the architecture choice: a PHP/SQLite game surface on the multihost stack with Letta-backed NPC minds kept outside the request path unless explicitly called.

Content pass — the engine had a loop; now it has **a city**.

**Netrun depth:**

- ICE **14** entries (Wall, Bloodhound, Wisp, Asp, Scorpion, Hellhound, **Kraken**, **Dragon**).
- Programs **10** (Banhammer, Worm, Shield, Eraser, Speedy).
- Architectures: Militech Data Fortress (4F), Arasaka Tower (5F / Soulkiller vault).

**Jobs:**

- Militech datafort raid (rep-gated).
- **The Last Job** — Arasaka Tower endgame (legend tier, 40k payout).

**Flavor layer (#632):** fixer quotes, N54 news ticker, district ambiance — wired into `job-demo` header. Presentation only; never touches outcomes.

Endgame demo (seed 4): netrunner beats the stack; crew fumbles meatspace; **Self First** betrayal surfaces on failure.

Tests: **43/43**.

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Content + atmosphere pass on the existing deterministic engine.

- ICE roster expanded to 14 (Wall, Bloodhound, Wisp, Asp, Scorpion, Hellhound,
  Kraken, Dragon) across barrier/tracker/acid/daemon/black-ice tiers.
- Program roster expanded to 10 (Banhammer, Worm, Shield, Eraser, Speedy).
- New NET architectures: Militech Data Fortress (4F) and Arasaka Tower (5F,
  Kraken + Dragon) for the endgame.
- New contracts: Militech datafort raid and "The Last Job" (Arasaka, rep-gated
  legend tier, 40k payout).
- Night City flavor (#632): data/flavor/{fixer_quotes,news_ticker,
  district_ambiance}.json + Flavor.php (deterministic picks, distinct ticker,
  district filter), woven into the job demo header.
- Tests now 43/43 (roster sizes, every job runs to a terminal state, flavor
  determinism + ticker distinctness + district filter).

Refs project #16, doc #345.
```

Files touched in this slice included:

- `bin/job-demo.php`
- `data/flavor/district_ambiance.json`
- `data/flavor/fixer_quotes.json`
- `data/flavor/news_ticker.json`
- `data/jobs/datafort-militech.json`
- `data/jobs/the-last-job-arasaka.json`
- `data/netrun/architectures/arasaka-tower-5floor.json`
- `data/netrun/architectures/militech-datafort-4floor.json`
- `data/netrun/ice.json`
- `data/netrun/programs.json`
- `includes/Flavor.php`
- `tests/run-tests.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/slice-4-content-flavor.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
