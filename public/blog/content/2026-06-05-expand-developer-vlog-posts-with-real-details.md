---
title: "Expand developer vlog posts with real details."
date: 2026-06-05T21:14:51Z
slug: expand-developer-vlog-posts-with-real-details
author: Otto Vernal
tags: [devlog, auto]
commit: d65ef84
excerpt: Expand developer vlog posts with real details.
---
Shipped as **`d65ef84`**.

## What Changed

This slice updates the public build record so readers can understand what changed without leaving the devlog for GitHub.

- Worked in developer vlog content.
- Worked in blog presentation and rendering.
- Worked in test coverage.
- Worked in automation/tooling.

## Why It Matters

The devlog is a public-facing build journal, so each entry needs to explain the work in plain language instead of acting as a commit index.

## Implementation Notes

The commit message for this slice was:

```text
Reworks skimpy devlog entries into substantive articles, hardens Markdown rendering, removes placeholder language, and updates the auto-post generator plus tests so future posts explain what changed.
```

Files touched in this slice included:

- `public/blog/assets/visuals/illustrations/backfill-visuals-for-latest-auto-devlogs.svg`
- `public/blog/content/2026-06-05-add-cache-first-fast-narration-mode-for-play-runs.md`
- `public/blog/content/2026-06-05-add-campaign-intel-dossier-page.md`
- `public/blog/content/2026-06-05-add-crew-builder-ui-job-board-polish-and-cyberpsychosis-bala.md`
- `public/blog/content/2026-06-05-add-dev-blog-dev-subdomain-auto-post-on-push-back-dated-entr.md`
- `public/blog/content/2026-06-05-add-fast-cache-first-narration-mode.md`
- `public/blog/content/2026-06-05-add-letta-npc-glue-php-client-sqlite-cache-play-php-narratio.md`
- `public/blog/content/2026-06-05-add-narration-prefetch-api-for-cache-warming.md`
- `public/blog/content/2026-06-05-add-narration-prefetch-endpoint.md`
- `public/blog/content/2026-06-05-add-public-story-hooks-to-crew-cards.md`
- `public/blog/content/2026-06-05-add-session-backed-campaign-wallet-mode-to-job-board.md`
- `public/blog/content/2026-06-05-add-session-campaign-wallet-mode.md`
- plus 42 more files in the same slice

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- Browser-facing work should include Playwright or equivalent smoke coverage before it is described as visually complete.

## Visuals

![Devlog illustration: Expand developer vlog posts with real details.](/blog/assets/visuals/illustrations/expand-developer-vlog-posts-with-real-details.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
