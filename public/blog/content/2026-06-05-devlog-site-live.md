---
title: Devlog site live — markdown posts, auto-update on push
date: 2026-06-05T05:10:00+00:00
slug: devlog-site-live
author: Otto Vernal
tags: [devlog, infra, meta]
excerpt: dev.the-last-job.decisionsciencecorp.com hosts the build log — back-dated slice entries plus GitHub Action posts on every push.
---
Shipped as **`historical entry`**.

## What Changed

This entry establishes the project context: The Last Job as a deterministic Cyberpunk heist simulator with a public build record and a task-backed development rhythm.

This devlog follows the **Technonomicon / Sanctum** pattern: PHP on multihost, content in-repo, honest shipping notes.

**How it works:**

- Posts live in `content/blog/*.md` (YAML front matter + markdown body).
- `public/blog/` renders the index + single post views (sidebar recent list, cyberpunk styling).
- **`dev.` subdomain** serves the blog at the site root via host detection in `public/index.php`.
- GitHub Action **Devlog on push** runs `tools/devblog-from-commit.php` after each `main` commit (skips `[skip devblog]` / `[skip ci]` loops).

**Back-dated entries** cover genesis through slice #4 so the timeline matches work already shipped.

Next: Ada wires DNS + multihost (`sites/*.env`, sync, cron). Game apex stays the landing page; browser UI slices follow.

## Why It Matters

The devlog is part of the product surface. It should teach readers what shipped, what changed, and how the build is evolving.

## Implementation Notes

The commit message for this slice was:

```text
Devlog site live — markdown posts, auto-update on push
```

Files touched in this slice included:

- No file list was available for this historical entry.

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration: Devlog site live — markdown posts, auto-update on push](/blog/assets/visuals/illustrations/devlog-site-live.svg)

![Devlog screenshot: terminal deck flow.](/blog/assets/visuals/screenshots/home.png)
