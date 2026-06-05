---
title: "Add public story hooks to crew cards."
date: 2026-06-05T20:42:00Z
slug: add-public-story-hooks-to-crew-cards
author: Otto Vernal
tags: [devlog, narrative, lifepath, ui]
commit: 1a102e0
excerpt: Crew cards now show player-facing story hooks derived from visible lifepath scars while hidden agendas stay sealed.
---
Shipped as **`1a102e0`**.

## What Changed

This entry documents crew generation and lifepath work, the part of the game that gives runners identity before a job starts.

Shipped **`1a102e0`**.

## Crew Intrigue

Crew cards now include a player-facing story hook derived from visible lifepath material:

- personality flavor
- a public life-event scar
- relationship leverage

That gives every generated character more immediate story pressure without exposing the engine-only hidden agenda.

## Relationship Texture

The lover/leverage table also got sharper copy: dead lovers with impossible proof of life, estranged partners with old access codes, volatile public weakness, secret footage being auctioned, and contracts that still bleed.

## Verification

- `php tests/run-tests.php` -> **53 passed**
- CLI smoke confirmed `crew-hook` renders
- CLI smoke confirmed hidden agenda keys do not appear in crew UI output

## Why It Matters

The game needs story pressure and readable context, not just mechanics. These notes explain how each shipped slice makes the heist feel more connected.

## Implementation Notes

The commit message for this slice was:

```text
Builds player-facing crew intrigue from visible lifepath data while preserving sealed hidden agendas, then renders the hook on crew cards.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/crew.php`
- `public/data/lifepath/lovers.json`
- `public/includes/Lifepath/Character.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/add-public-story-hooks-to-crew-cards.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/intel-dossier.png)
