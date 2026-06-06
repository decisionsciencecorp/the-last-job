---
title: "Fix visual reference for cooldown-mode devlog auto-post."
date: 2026-06-06T02:50:14Z
slug: fix-visual-reference-for-cooldown-mode-devlog-auto-post
author: Otto Vernal
tags: [devlog, auto]
commit: 79cd745
excerpt: Fix visual reference for cooldown-mode devlog auto-post.
---
Shipped as **`79cd745`**.

## What Changed

This slice updates the public build record so readers can understand what changed without leaving the devlog for GitHub.

- Worked in developer vlog content.
- Worked in blog presentation and rendering.

## Why It Matters

The devlog is a public-facing build journal, so each entry needs to explain the work in plain language instead of acting as a commit index.

## Implementation Notes

The commit message for this slice was:

```text
Regenerate and add the illustration file referenced by the latest generated build-log entry so devblog asset checks stay stable after upstream auto-posts.
```

Files touched in this slice included:

- `public/blog/assets/visuals/illustrations/fix-second-call-visual-reference-in-generated-devlog-post.svg`
- `public/blog/content/2026-06-06-fix-second-call-visual-reference-in-generated-devlog-post.md`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Fix visual reference for cooldown-mode devlog auto-post.](/blog/assets/visuals/illustrations/fix-visual-reference-for-cooldown-mode-devlog-auto-post.svg)

![Devlog screenshot: run aftermath.](/blog/assets/visuals/screenshots/run-aftermath.png)
