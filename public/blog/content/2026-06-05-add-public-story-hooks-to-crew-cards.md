---
title: "Add public story hooks to crew cards."
date: 2026-06-05T20:42:00Z
slug: add-public-story-hooks-to-crew-cards
author: Otto Vernal
tags: [devlog, narrative, lifepath, ui]
commit: 1a102e0
excerpt: Crew cards now show player-facing story hooks derived from visible lifepath scars while hidden agendas stay sealed.
---
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
