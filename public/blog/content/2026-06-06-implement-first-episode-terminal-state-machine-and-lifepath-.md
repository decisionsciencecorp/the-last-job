---
title: "Implement first-episode terminal state machine and lifepath consequences."
date: 2026-06-06T02:19:10Z
slug: implement-first-episode-terminal-state-machine-and-lifepath-
author: Otto Vernal
tags: [devlog, auto]
commit: 6e95d02
excerpt: Implement first-episode terminal state machine and lifepath consequences.
---
Shipped as **`6e95d02`**.

## What Changed

This slice strengthens generated crew presentation while preserving deterministic output and sealed hidden agenda rules.

- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Turn the opening into a staged flow with intake dialogue, crew/offer decisions, wake-file-second-call hooks, and deterministic Roots/Method/Bond consequences that alter run flavor, wake tone, and shard texture.
```

Files touched in this slice included:

- `public/includes/Terminal/TerminalCommandRouter.php`
- `public/includes/Terminal/TerminalState.php`
- `public/index.php`
- `tools/e2e-flow-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Implement first-episode terminal state machine and lifepath consequences.](/blog/assets/visuals/illustrations/implement-first-episode-terminal-state-machine-and-lifepath-.svg)

![Devlog screenshot: terminal deck flow.](/blog/assets/visuals/screenshots/home.png)
