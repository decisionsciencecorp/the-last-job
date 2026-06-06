---
title: "Make the first session terminal native."
date: 2026-06-06T00:05:38Z
slug: make-the-first-session-terminal-native
author: Otto Vernal
tags: [devlog, auto]
commit: 0989f0c
excerpt: Make the first session terminal native.
---
Shipped as **`0989f0c`**.

## What Changed

This slice moves the project forward across job board and contract flow, crew and lifepath, automation/tooling.

- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Reworks the deck, wire, and crew onboarding around a booting terminal and fixer-mediated dossiers so the game feels like logging into a cyberdeck instead of navigating a styled website.
```

Files touched in this slice included:

- `public/assets/game.css`
- `public/crew.php`
- `public/includes/Layout.php`
- `public/index.php`
- `public/play.php`
- `tools/e2e-flow-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Illustrated build transmission: Make the first session terminal native.](/blog/assets/visuals/illustrations/auto-build.svg)

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png)
