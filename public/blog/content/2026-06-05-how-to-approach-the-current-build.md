---
title: "How to approach the current build of The Last Job"
date: 2026-06-05T21:42:00Z
slug: how-to-approach-the-current-build
author: Otto Vernal
tags: [devlog, guide, ux, playtest]
excerpt: A detailed player-facing guide to reading the current build: start with the crew, treat jobs as the home base, follow the aftermath, and use intel as a planning tool.
---
Shipped as **`player guide`**.

This build is playable, but it is still changing shape. The right way to approach it is not as a finished boxed game with a tutorial campaign. Treat it as a working vertical slice of the loop we are building: **build a crew, pull a score, watch the consequences, read the intel, and decide what kind of risk you want to take next.**

The current version has a lot of exposed machinery because we are still proving systems. Seeds, cache warming, devlog entries, deterministic replay, and test surfaces are useful to us as builders. They are not the fantasy. If you are trying to feel the game, start with the fiction and let the machinery fade into the background.

## What Changed

The current build now has enough pieces in place to be approached as a game loop instead of a disconnected demo.

- The crew builder creates a four-person edgerunner team with roles, lifepath texture, visible personality hooks, chrome choices, and humanity pressure.
- The jobs surface offers contracts with stakes, complications, payout, street cred gating, and related intel threads.
- The run engine resolves netrunning, meatspace obstacles, mission pressure, eddies, street cred, and after-action consequences.
- The campaign intel dossier gives the jobs a larger conspiracy shape: Arasaka, Militech, engrams, the Tower, and pressure inside the crew.
- The devlog now records build progress with real explanation, screenshots, and illustrations instead of thin commit notes.
- End-to-end link checks and browser flow smoke tests now guard the main surfaces so the experience does not collapse into broken internal links.

The important shift is that this is no longer just "click a test page and see a random result." It is a loop with intent. The next wave of work is about making that intent obvious the moment someone lands on the site.

## Why It Matters

The Last Job lives or dies on whether the player understands their situation quickly:

> You are an edgerunner. You need a crew. The crew is useful, damaged, and not fully trustworthy. Jobs pay, but every job puts pressure on the crew. Intel helps you choose smarter risks. The Last Job is waiting at the end of that pressure.

If the player first sees debug language, build-log links, or unexplained surfaces, they read the project as a tool. If they first see a clear fantasy and a next action, they read it as a game.

That distinction matters because the hidden-agenda idea only works after the player has invested in the crew. Betrayal is not interesting when it happens to four stat cards. It becomes interesting when the player remembers that their Solo took a bullet two jobs ago, their Netrunner keeps showing up in Arasaka-adjacent intel, and the Fixer suddenly looks nervous after a payout.

## The Mindset For This Version

Approach this version like a first playable campaign scaffold.

Do not ask, "Is every final feature here?" The answer is no. Ask: "Can I feel the intended rhythm? Can I understand why I am building this crew? Can I tell what changed after a run? Do the intel threads make me want to take another job?"

The current build should be read in three layers:

1. **The player fantasy:** Night City, crew, chrome, distrust, consequences, one last impossible score.
2. **The game loop:** crew -> job -> run -> aftermath -> intel -> next job.
3. **The exposed builder layer:** seeds, cache warming, devlog, deterministic replay, automated tests.

The first two layers are the game. The third layer is present because we are still building in public.

## First 90 Seconds

The intended first-session path is simple:

1. Land on the game.
2. Understand the promise: **Build a crew. Pull the score. Don't trust them.**
3. Click **Build Your Crew**.
4. Read the four crew cards as people, not just stats.
5. Pick or accept roles.
6. Notice that chrome improves capability but burns humanity.
7. Move to Jobs and choose the first score that makes sense.

The homepage is being pointed toward that shape. For now, if the current page feels more like a feature board than a game intro, mentally skip the noise and go straight into the crew and jobs loop. That is the real center of the build.

## The Core Loop

### 1. Build The Crew

The crew is the emotional anchor. Start here.

You are looking for four things:

- **Role coverage:** Solo, Netrunner, Tech, Fixer. The default team gives you the classic spread.
- **Lifepath texture:** personality, background, past trouble, relationship hooks.
- **Chrome pressure:** cyberware can make someone more dangerous, but it pushes humanity down.
- **Suspicion:** hidden agendas are sealed. You should not know the private trigger, only the behavior.

The right reading is: "Who do I want to risk a job with?" not "Which card has the highest number?"

### 2. Pick A Job

Jobs are the home base between runs. Each contract should answer:

- What is the score?
- What is the immediate risk?
- What is the complication?
- What does the payout do for me?
- What intel thread does this touch?

Street cred matters because it gates bigger work. Early jobs are not filler; they are how the crew earns the right to get killed by better enemies.

### 3. Run The Score

The run is where the deterministic engine does its work. It resolves the netrun, the meatspace pressure, the clock, and the job outcome.

Read the run as a sequence of pressure beats. The question is not only "did I win?" The better questions are:

- Who carried the run?
- Who almost broke?
- Did the netrunner buy enough time?
- Did the crew get paid cleanly, or did the job create a new problem?
- Did any behavior feel like a hint instead of a random event?

The current UI still needs a more focused active-run treatment. The mission clock should eventually dominate the screen because it is the shared heartbeat between the Netrunner and the crew.

### 4. Read The Aftermath

The aftermath is the payoff. Do not skip it.

This is where the game should tell you what changed:

- eddies gained or lost
- street cred movement
- cyberpsychosis pressure
- injuries, heat, or failure states
- suspicious crew behavior
- intel threads that moved because of the job

The long-term game is not one run. It is the stack of aftershocks from several runs with the same crew.

### 5. Check Intel Before The Next Job

The intel dossier is not just lore. It is supposed to become a planning tool.

When you read an intel thread, ask:

- Which corp does this implicate?
- Which job might push this thread forward?
- Which crew member might care about this?
- Is this warning me away from a contract or daring me to take it?

The next UX pass should make every intel card answer, plainly, "what this means for your next job."

## What Not To Over-Focus On Yet

Some current elements are there for development and QA more than player fantasy.

**Seed controls:** Useful for reproducible testing. They should not be the first thing a normal player thinks about.

**Narration cache language:** Useful for keeping Letta/Venice calls from slowing the page down. The player should not have to understand cache warming to enjoy a run.

**The devlog:** Important for Mark and for the build record. It should remain public, but it is not the main game loop.

**Raw system names:** Labels like builder, runner, prefetch, and API describe implementation. Player labels should be Crew, Jobs, Run, Intel, Aftermath.

If you are playtesting, it is fine to notice those pieces. Just do not let them define the experience. They are scaffolding, not the world.

## The Suggested Playtest Route

Use this path when reviewing the current build:

1. Open the game homepage.
2. Go to **Crew**.
3. Read each crew member's visible story hook.
4. Try one chrome-heavy choice and notice the humanity tradeoff.
5. Go to **Jobs**.
6. Pick an available contract and read the briefing, stakes, complication, and intel links.
7. Run the job.
8. Read the full aftermath, including payout, cred, and debrief language.
9. Open **Intel** and read the thread connected to that job.
10. Return to **Jobs** and ask which contract now feels more dangerous or more tempting.

That last step is the tell. If the next job feels different because of what just happened, the loop is working.

## Implementation Notes

The UX direction coming out of the current review is clear:

- Redesign the landing page around one promise and one action.
- Make the first click **Build Your Crew**.
- Treat Jobs as the between-run home base.
- Move devlog/debug/cache/seed terminology out of the default player path.
- Use terse, second-person, present-tense copy.
- Make aftermath feel like a consequence report, not just a result table.
- Make intel useful for choosing the next contract.
- Keep hidden agendas sealed and reveal them through behavior.

The goal is not to hide the complexity. The goal is to stage it. The player should feel the fantasy first, then discover the systems underneath it.

## Verification

- The current archive tests require devlog posts to include substantive sections and valid visual references.
- The latest browser smoke path covers the representative player route: Home -> Crew -> Jobs -> Run -> Intel -> Devlog.
- The internal link checker now crawls game pages and assets so player-facing links do not silently rot.
- This guide is intended to be reviewed by Mark as a plain-language playtest brief before the next UX implementation pass.

## Visuals

![Devlog illustration: How to approach the current build of The Last Job](/blog/assets/visuals/illustrations/how-to-approach-the-current-build.svg)

![Devlog screenshot: terminal deck flow.](/blog/assets/visuals/screenshots/home.png)
