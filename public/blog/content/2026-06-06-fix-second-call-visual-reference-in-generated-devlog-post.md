---
title: "Fix second-call visual reference in generated devlog post."
date: 2026-06-06T02:44:22Z
slug: fix-second-call-visual-reference-in-generated-devlog-post
author: Otto Vernal
tags: [devlog, auto]
commit: efe8f16
excerpt: Fix second-call visual reference in generated devlog post.
---
Shipped as **`efe8f16`**.

## What Changed

This slice updates the public build record so readers can understand what changed without leaving the devlog for GitHub.

- Worked in developer vlog content.
- Worked in blog presentation and rendering.

## Why It Matters

The devlog is a public-facing build journal, so each entry needs to explain the work in plain language instead of acting as a commit index.

## Implementation Notes

The commit message for this slice was:

```text
Regenerate the matching illustration asset and update the post reference so devblog visual integrity tests remain green after auto-post generation.
```

Files touched in this slice included:

- `public/blog/assets/visuals/illustrations/add-generated-illustration-for-second-call-packet-devlog-pos.svg`
- `public/blog/content/2026-06-06-add-generated-illustration-for-second-call-packet-devlog-pos.md`

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Fix second-call visual reference in generated devlog post.](/blog/assets/visuals/illustrations/fix-second-call-visual-reference-in-generated-devlog-post.svg)

![Devlog screenshot: verification rig.](/blog/assets/visuals/screenshots/devlog-index.png)
