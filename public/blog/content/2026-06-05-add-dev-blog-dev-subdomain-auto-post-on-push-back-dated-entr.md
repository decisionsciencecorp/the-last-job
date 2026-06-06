---
title: "Add dev blog (dev subdomain) + auto-post on push + back-dated entries"
date: 2026-06-05T05:09:40Z
slug: add-dev-blog-dev-subdomain-auto-post-on-push-back-dated-entr
author: Otto Vernal
tags: [devlog, auto]
commit: 008e3f3
excerpt: Add dev blog (dev subdomain) + auto-post on push + back-dated entries
---
Shipped as **`008e3f3`**.

## What Changed

This pass creates the developer vlog itself: a repo-backed publication channel for build notes, deployment updates, and project history.

- Worked in developer vlog visual assets.
- Worked in blog rendering.
- Worked in crew and lifepath.
- Worked in test coverage.
- Worked in automation/tooling.

## Why It Matters

The point is to keep the project understandable as it ships, so build decisions remain visible to players, collaborators, and future development passes.

## Implementation Notes

The commit message for this slice was:

```text
Markdown devlog patterned after Technonomicon/Sanctum blog shape:
- content/blog/*.md with front matter; public/blog/ PHP renderer + CSS
- dev.* host serves blog at site root (public/index.php host routing)
- 8 back-dated posts (genesis through slice #4) + devlog launch entry
- tools/devblog-from-commit.php + GitHub Action posts on every main push
- Tests: devblog load/sort (46/46 total)

Ada handoff: dev.the-last-job.decisionsciencecorp.com + game apex provisioning.
```

Files touched in this slice included:

- `.github/workflows/devblog.yml`
- `content/blog/2026-06-05-architecture-lemp-letta.md`
- `content/blog/2026-06-05-devlog-site-live.md`
- `content/blog/2026-06-05-genesis-athena-and-the-board.md`
- `content/blog/2026-06-05-slice-1-5-lifepath-crew.md`
- `content/blog/2026-06-05-slice-1-netrun-engine.md`
- `content/blog/2026-06-05-slice-2-skill-checks-humanity.md`
- `content/blog/2026-06-05-slice-3-job-loop.md`
- `content/blog/2026-06-05-slice-4-content-flavor.md`
- `includes/Blog/Blog.php`
- `includes/Blog/Markdown.php`
- `public/blog/assets/style.css`
- plus 5 more files in the same slice

## Verification

- The change was committed to `main` and included in the running devlog timeline.
- The current archive test suite verifies devlog posts render with substantive body content, illustrations, screenshots, and valid asset references.
- For browser-facing slices, Playwright smoke checks in this build cycle covered the active game surfaces and devlog pages.


## Visuals

![Devlog illustration: Add dev blog (dev subdomain) + auto-post on push + back-dated entries](/blog/assets/visuals/illustrations/add-dev-blog-dev-subdomain-auto-post-on-push-back-dated-entr.svg)

![Devlog screenshot: crew dossier surface.](/blog/assets/visuals/screenshots/crew-builder.png)
