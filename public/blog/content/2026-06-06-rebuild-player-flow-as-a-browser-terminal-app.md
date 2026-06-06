---
title: "Rebuild player flow as a browser terminal app."
date: 2026-06-06T00:27:38Z
slug: rebuild-player-flow-as-a-browser-terminal-app
author: Otto Vernal
tags: [devlog, auto]
commit: ef01ed2
excerpt: Rebuild player flow as a browser terminal app.
---
Shipped as **`ef01ed2`**.

## What Changed

This slice moves the project forward across automation/tooling.

- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Adds a session-backed command API and terminal command layer so the main experience runs through typed deck commands instead of themed page navigation.
```

Files touched in this slice included:

- `public/api/terminal-command.php`
- `public/assets/game.css`
- `public/includes/Terminal/TerminalCommandRouter.php`
- `public/includes/Terminal/TerminalState.php`
- `public/index.php`
- `tools/e2e-flow-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Illustrated build transmission: Rebuild player flow as a browser terminal app.](/blog/assets/visuals/illustrations/auto-build.svg)

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png)
