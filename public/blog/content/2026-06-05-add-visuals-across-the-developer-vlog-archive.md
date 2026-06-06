---
title: "Add visuals across the developer vlog archive."
date: 2026-06-05T21:02:16Z
slug: add-visuals-across-the-developer-vlog-archive
author: Otto Vernal
tags: [devlog, auto]
commit: 5945286
excerpt: Add visuals across the developer vlog archive.
---
Shipped as **`5945286`**.

## What Changed

This pass upgrades the developer vlog from a text-only changelog into a visual build record with illustrations, screenshots, and renderer support for figures.

- Worked in developer vlog content.
- Worked in developer vlog visual assets.
- Worked in blog rendering.
- Worked in crew and lifepath.
- Worked in test coverage.
- Worked in automation/tooling.

## Why It Matters

The build record is easier to scan and more credible when each post shows the thing being discussed, not just a commit hash.

## Implementation Notes

The commit message for this slice was:

```text
Adds Markdown figure support, branded illustrations, real UI screenshots, visual sections for every existing devlog post, and validation so future posts keep illustration and screenshot coverage.

```

Files touched in this slice included:

- `public/blog/assets/style.css`
- `public/blog/assets/visuals/illustrations/add-cache-first-fast-narration-mode-for-play-runs.svg`
- `public/blog/assets/visuals/illustrations/add-campaign-intel-dossier-page.svg`
- `public/blog/assets/visuals/illustrations/add-crew-builder-ui-job-board-polish-and-cyberpsychosis-bala.svg`
- `public/blog/assets/visuals/illustrations/add-dev-blog-dev-subdomain-auto-post-on-push-back-dated-entr.svg`
- `public/blog/assets/visuals/illustrations/add-fast-cache-first-narration-mode.svg`
- `public/blog/assets/visuals/illustrations/add-letta-npc-glue-php-client-sqlite-cache-play-php-narratio.svg`
- `public/blog/assets/visuals/illustrations/add-narration-prefetch-api-for-cache-warming.svg`
- `public/blog/assets/visuals/illustrations/add-narration-prefetch-endpoint.svg`
- `public/blog/assets/visuals/illustrations/add-public-story-hooks-to-crew-cards.svg`
- `public/blog/assets/visuals/illustrations/add-session-backed-campaign-wallet-mode-to-job-board.svg`
- `public/blog/assets/visuals/illustrations/add-session-campaign-wallet-mode.svg`
- plus 93 more files in the same slice

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Add visuals across the developer vlog archive.](/blog/assets/visuals/illustrations/add-visuals-across-the-developer-vlog-archive.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
