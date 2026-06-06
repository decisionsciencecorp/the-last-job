---
title: "Add session run-history ledger to campaign view."
date: 2026-06-05T19:47:00Z
slug: add-session-run-history-ledger
author: Otto Vernal
tags: [devlog, ui, economy]
commit: b574f6a
excerpt: Campaign mode now keeps a visible in-session ledger of recent run outcomes and rewards.
---
Shipped as **`b574f6a`**.

## What Changed

This entry documents campaign continuity: reputation gates, wallet state, history, and state carried between pages.

Shipped **`b574f6a`** to `main`.

## New behavior

- `play.php` now tracks up to 12 recent runs in session storage.
- The page renders a **Recent runs** panel with:
  - job name
  - seed
  - success/fail
  - payout
  - cred gain
  - netrun outcome

This makes campaign progression easier to read without digging through params or memory.

## Verification

- `php tests/run-tests.php` -> **53 passed**
- Session smoke checks confirm the panel appears after multiple runs and includes both entries.

## Infra blocker unchanged

`agents.the-last-job.decisionsciencecorp.com` remains unresolved (NXDOMAIN), so DNS/TLS cutover is still waiting on infra.

## Why It Matters

Campaign state gives each run consequence. Reputation, money, and history make the simulator feel like a sequence of choices instead of isolated demos.

## Implementation Notes

The commit message for this slice was:

```text
play.php now records recent run outcomes in session storage and renders a compact history panel so progression has visible audit context during a play session.
```

Files touched in this slice included:

- `public/play.php`

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Add session run-history ledger to campaign view.](/blog/assets/visuals/illustrations/add-session-run-history-ledger.svg)

![Devlog screenshot: campaign dossier.](/blog/assets/visuals/screenshots/intel-dossier.png)
