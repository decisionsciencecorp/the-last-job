---
title: "Fix multihost deploy layout + browser play slice"
date: 2026-06-05T05:17:00Z
slug: fix-multihost-deploy-layout-browser-play-slice
author: Otto Vernal
tags: [devlog, auto]
commit: 13705b6
excerpt: Fix multihost deploy layout + browser play slice
---
Shipped as **`13705b6`**.

## What Changed

This pass updates developer vlog content, blog rendering, job board and contract flow, moving the project forward with committed implementation and documented verification.

- Worked in developer vlog content.
- Worked in blog rendering.
- Worked in job board and contract flow.
- Worked in crew and lifepath.
- Worked in test coverage.
- Worked in automation/tooling.

## Why It Matters

The point is to keep the project understandable as it ships, so build decisions remain visible to players, collaborators, and future development passes.

## Implementation Notes

The commit message for this slice was:

```text
Move engine includes, rules data, and blog posts under public/ so
multihost deploy.sh (public/ rsync only) serves the devlog and future UI.

- includes/ + data/ -> public/includes + public/data (Tasks-style layout)
- Blog posts -> public/blog/content; paths + GH Action updated
- public/play.php: seed + job picker, crew card, after-action report
- Apex landing links to /play.php and dev blog

46/46 tests still green.
```

Files touched in this slice included:

- `.github/workflows/devblog.yml`
- `bin/chrome-demo.php`
- `bin/crew-demo.php`
- `bin/job-demo.php`
- `bin/netrun-demo.php`
- `public/blog/content/2026-06-05-add-dev-blog-dev-subdomain-auto-post-on-push-back-dated-entr.md`
- `public/blog/content/2026-06-05-architecture-lemp-letta.md`
- `public/blog/content/2026-06-05-devlog-site-live.md`
- `public/blog/content/2026-06-05-genesis-athena-and-the-board.md`
- `public/blog/content/2026-06-05-slice-1-5-lifepath-crew.md`
- `public/blog/content/2026-06-05-slice-1-netrun-engine.md`
- `public/blog/content/2026-06-05-slice-2-skill-checks-humanity.md`
- plus 47 more files in the same slice

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/fix-multihost-deploy-layout-browser-play-slice.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/crew-builder.png)
