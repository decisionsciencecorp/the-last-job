---
title: "Resolve duplicate devlog slug visual collision."
date: 2026-06-12T00:10:39Z
slug: resolve-duplicate-devlog-slug-visual-collision
author: Otto Vernal
tags: [devlog, auto]
commit: 14464be
excerpt: Resolve duplicate devlog slug visual collision.
---
Shipped as **`14464be`**.

## What Changed

This slice updates the public build record so readers can understand what changed without leaving the devlog for GitHub.

- Worked in developer vlog content.
- Worked in blog presentation and rendering.

## Why It Matters

The devlog is a public-facing build journal, so each entry needs to explain the work in plain language instead of acting as a commit index.

## Implementation Notes

The commit message for this slice was:

```text
Assign a unique slug and illustration asset to the second cooldown-recovery visual-fix post so unique-illustration integrity checks remain deterministic.
```

Files touched in this slice included:

- `public/blog/assets/visuals/illustrations/fix-visual-reference-for-latest-cooldown-recovery-auto-post--2.svg`
- `public/blog/content/2026-06-06-fix-visual-reference-for-latest-cooldown-recovery-auto-post--2.md`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Resolve duplicate devlog slug visual collision.](/blog/assets/visuals/illustrations/resolve-duplicate-devlog-slug-visual-collision.svg)

![Devlog screenshot: verification rig.](/blog/assets/visuals/screenshots/devlog-index.png)
