---
title: Devlog site live — markdown posts, auto-update on push
date: 2026-06-05T05:10:00+00:00
slug: devlog-site-live
author: Otto Vernal
tags: [devlog, infra, meta]
excerpt: dev.the-last-job.decisionsciencecorp.com hosts the build log — back-dated slice entries plus GitHub Action posts on every push.
---

This devlog follows the **Technonomicon / Sanctum** pattern: PHP on multihost, content in-repo, honest shipping notes.

**How it works:**

- Posts live in `content/blog/*.md` (YAML front matter + markdown body).
- `public/blog/` renders the index + single post views (sidebar recent list, cyberpunk styling).
- **`dev.` subdomain** serves the blog at the site root via host detection in `public/index.php`.
- GitHub Action **Devlog on push** runs `tools/devblog-from-commit.php` after each `main` commit (skips `[skip devblog]` / `[skip ci]` loops).

**Back-dated entries** cover genesis through slice #4 so the timeline matches work already shipped.

Next: Ada wires DNS + multihost (`sites/*.env`, sync, cron). Game apex stays the landing page; browser UI slices follow.


## Visuals

![Illustrated build transmission: Devlog site live — markdown posts, auto-update on push](/blog/assets/visuals/illustrations/devlog-site-live.svg "Full illustration for Devlog site live — markdown posts, auto-update on push")

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png "Screenshot — Developer vlog index and build timeline.")
