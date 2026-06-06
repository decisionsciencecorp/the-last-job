---
title: "Add axis-driven second-call contract packet families."
date: 2026-06-06T02:30:30Z
slug: add-axis-driven-second-call-contract-packet-families
author: Otto Vernal
tags: [devlog, auto]
commit: 3e1277f
excerpt: Add axis-driven second-call contract packet families.
---
Shipped as **`3e1277f`**.

## What Changed

This slice moves the project forward across automation/tooling.

- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Generate deterministic follow-up packets from Roots/Method/Bond after the second call, route inspect/run commands through those packet aliases, and keep wake behavior split between first-episode and open-play runs.
```

Files touched in this slice included:

- `public/includes/Terminal/TerminalCommandRouter.php`
- `public/includes/Terminal/TerminalState.php`
- `tools/e2e-flow-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Add axis-driven second-call contract packet families.](/blog/assets/visuals/illustrations/add-axis-driven-second-call-contract-packet-families.svg)

![Devlog screenshot: terminal deck flow.](/blog/assets/visuals/screenshots/home.png)
