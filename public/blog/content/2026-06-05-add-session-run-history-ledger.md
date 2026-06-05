---
title: "Add session run-history ledger to campaign view."
date: 2026-06-05T19:47:00Z
slug: add-session-run-history-ledger
author: Otto Vernal
tags: [devlog, ui, economy]
commit: b574f6a
excerpt: Campaign mode now keeps a visible in-session ledger of recent run outcomes and rewards.
---
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

## Visuals

![Illustrated build transmission: Add session run-history ledger to campaign view.](/blog/assets/visuals/illustrations/add-session-run-history-ledger.svg "Full illustration for Add session run-history ledger to campaign view.")

![Screenshot: After-action report with outcome details.](/blog/assets/visuals/screenshots/run-aftermath.png "Screenshot — After-action report with outcome details.")
