---
title: "Fix broken play links and add link-check coverage."
date: 2026-06-05T21:28:22Z
slug: fix-broken-play-links-and-add-link-check-coverage
author: Otto Vernal
tags: [devlog, auto]
commit: 32eca72
excerpt: Fix broken play links and add link-check coverage.
---
Shipped as **`32eca72`**.

## What Changed

This slice moves the project forward across job board and contract flow, crew and lifepath, test coverage, automation/tooling.

- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in test coverage.
- Worked in automation/tooling.

## Why It Matters

Keeping the explanation alongside the implementation makes the build easier to review, test, and continue.

## Implementation Notes

The commit message for this slice was:

```text
Removes the raw narration prefetch API link from the job board, makes the crew page immediately playable, and adds an internal link crawler plus regression coverage for the broken prefetch anchor.
```

Files touched in this slice included:

- `public/crew.php`
- `public/play.php`
- `tests/run-tests.php`
- `tools/e2e-link-check.py`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Fix broken play links and add link-check coverage.](/blog/assets/visuals/illustrations/fix-broken-play-links-and-add-link-check-coverage.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
