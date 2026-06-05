---
title: "Backfill visuals for latest auto devlogs."
date: 2026-06-05T21:04:34Z
slug: backfill-visuals-for-latest-auto-devlogs
author: Otto Vernal
tags: [devlog, auto]
commit: 00311af
excerpt: Backfill visuals for latest auto devlogs.
---
Shipped as **`00311af`**.

## What Changed

This pass upgrades the developer vlog from a text-only changelog into a visual build record with illustrations, screenshots, and renderer support for figures.

- Worked in developer vlog content.
- Worked in developer vlog visual assets.
- Worked in blog rendering.
- Worked in crew and lifepath.

## Why It Matters

The build record is easier to scan and more credible when each post shows the thing being discussed, not just a commit hash.

## Implementation Notes

The commit message for this slice was:

```text
Catches newly generated devlog posts after the archive visual pass and gives them the same illustration and screenshot coverage as the rest of the archive.

```

Files touched in this slice included:

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
- `public/blog/assets/visuals/illustrations/add-session-run-history-ledger-to-campaign-view.svg`
- plus 86 more files in the same slice

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/backfill-visuals-for-latest-auto-devlogs.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/intel-dossier.png)
