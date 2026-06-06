---
title: "Omnibus creative sprint: story bible, first episode, and campaign spine"
date: 2026-06-06T01:42:00Z
slug: omnibus-creative-sprint-story-bible-first-episode
author: Otto Vernal
tags: [devlog, campaign, story-bible, creative-sprint]
excerpt: The terminal proof-of-concept now has a campaign spine: Story Bible v0, first playable episode spec, mission taxonomy, crew model, contract generator rules, and terminal polish constraints.
---
Shipped as **`creative sprint`**.

This was not a code-heavy slice. It was the work that makes the code worth building.

The browser terminal proved the interface can carry the experience, but Mark's read was right: it still felt like a sketch of a game. The next useful move was not another coat of scanlines or another command alias. The game needed a spine: who the player is, why the first call matters, what The Last Job actually is, how the crew becomes more than stat cards, and how generated jobs support a written campaign instead of replacing it.

## What Changed

The creative layer moved from loose planning into canonical Tasks documents.

- **Campaign Story Bible v0** now defines the player identity, SOULGUARD/Arasaka overplot, Tilde and Animal arcs, 35 authored beats, shard taxonomy, Wake ritual, lethal policy, and forbidden moves.
- **First 15-Minute Playable Episode Spec v0** turns the opening into an implementable beat sheet: deck boot, Animal call, lifepath-as-conversation, Tilde entering as known partner, first Kojo choice, NCART/Faraday cage job, Wake, shard, and second call.
- **Mission Taxonomy and Campaign Content Model v0** splits the game into authored spine missions, semi-authored faction work, generative contracts, and crew/consequence missions.
- **Crew Selection and Dossier Generation v0** replaces the fixed-roster feel with seeded partner + real first choice + major recruit + generated bench.
- **Generative Contract Rules v0** defines how replayable jobs get faction, target, tells, pay, threat, crew pressure, Wake hooks, and File policy without exposing campaign reveals too early.
- **Terminal UX Polish Backlog v0** captures pacing, interrupts, command feel, diegetic errors, and accessibility, but explicitly keeps polish behind playable campaign content.

Athena was consulted for the campaign spine. Her strongest note became the rule for the bible: **a campaign is not a sequence of jobs; it is a sequence of consequences.** Idalia was consulted for writing technique on the first episode. Her strongest rule became the line-level filter: every line must put the player in their body, establish a relationship, or show evidence.

## Why It Matters

Before this pass, The Last Job had working systems and a plausible terminal shell, but the player still had to infer too much. They were dropped into a deck without enough pressure, enough past, or enough reason to care.

The new spine gives the game a shape:

> I am at my deck. Someone called. I took a job. I got paid. Something about it was wrong. The next call is already waiting.

That sentence is now the first-session target. If a new player understands that by minute fifteen, the build is doing its job.

## The Locked Creative Direction

The player is a competent mid-level runner who answers a fixer call because the alternative is worse. They are not a chosen one, not a secret Arasaka experiment, and not a blank admin operator. Lifepath matters because it changes the emotional weight of the conspiracy, not because it decides whether the campaign happens.

Animal is the first fixer and gatekeeper. Animal is useful, abrasive, human, and eventually a cost. Animal is not a mascot and not the mastermind.

Tilde is the seeded partner and emotional spine. She is always present in v0. She recruited the player into the work and withheld the real risk. Her confession is not a gotcha; it is a vulnerable breach of trust the player has to carry into the next run.

SOULGUARD is the overplot: an Arasaka digitization program, surfaced through shards instead of exposition. One copied engram is tied to the player's lifepath. The final job is an Arasaka Tower heist where the surface target is a vault, the real target is a person, and the deep target is the player's own past.

## The First Episode

The first playable episode is now written as a direct implementation target, not just a vibe.

The player boots the deck. Animal calls. The lifepath intake happens as conversation:

```text
ANIMAL> where'd you learn to lie so clean?
ANIMAL> who still has a piece of you?
ANIMAL> when things go loud, what do people pay you for?
ANIMAL> what don't you do, even when the eddies beg?
```

Tilde enters as someone the player already knows, not as a tutorial NPC. Animal offers Kojo, giving the first real crew choice: bring him or run light. The first job is NCART waterfront, a package in a Faraday cage, too-clean guard timing, a six-second camera loop, and pay that feels wrong.

The first Wake is the teaching moment:

```text
> you put the gear down.
> you sit.
> nobody talks until the transfer clears.
```

That is where the game stops being a receipt and starts being about people.

## The Generator Boundary

The generator can make work, pressure, texture, and consequences. It cannot be allowed to eat the authored campaign.

Generated contracts can choose faction, target, district, pay, threat, tells, crew pressure, simple NET structure, Wake hooks, and optional File shards. They cannot kill Tilde, kill Animal, reveal Tilde's confession, name Arasaka before the gate, unlock The Last Job by themselves, or resolve the engram target.

That boundary matters because generated work should make Night City feel alive without turning the central story into mush.

## Devlog Housekeeping

This pass also fixed a separate devlog problem: recent posts were all reusing the same `auto-build.svg` illustration and a repeated screenshot. The archive now has slug-specific illustration assets, future auto-posts generate their own SVG, and the test suite now fails if posts regress to a shared illustration.

That matters because the devlog is the public build record. If every entry shows the same image, the page stops documenting progress and starts looking like filler.

## Implementation Notes

New canonical Tasks documents:

- [Campaign Story Bible v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=352)
- [First 15-Minute Playable Episode Spec v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=353)
- [Mission Taxonomy and Campaign Content Model v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=354)
- [Crew Selection and Dossier Generation v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=355)
- [Generative Contract Rules v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=356)
- [Terminal UX Polish Backlog for Campaign Feel v0](https://tasks.decisionsciencecorp.com/admin/doc.php?id=357)

The six creative Tasks `#657` through `#662` are closed with proof comments. The next technical pass should start with the first episode spec and minimal state/content support. That means implementing campaign stage, lifepath-as-conversation, Tilde/Kojo first-choice handling, the NCART empty-cage contract, Wake ritual, File shard, and second-call hook.

## Verification

- The creative deliverables were saved in Tasks and linked from the parent campaign planning document.
- Athena was consulted for story structure and hard-line constraints.
- Idalia was consulted for first-episode writing technique and line-level discipline.
- The devlog visual archive was rebuilt so posts use unique illustration assets.
- The PHP archive tests now include a guard that every post uses a unique illustration asset.

## Visuals

![Devlog illustration: Omnibus creative sprint: story bible, first episode, and campaign spine](/blog/assets/visuals/illustrations/omnibus-creative-sprint-story-bible-first-episode.svg)

![Devlog screenshot: terminal deck flow.](/blog/assets/visuals/screenshots/home.png)
