---
title: "Agents DNS+TLS cutover complete; narrate 504 mitigated."
date: 2026-06-05T20:01:30Z
slug: agents-dns-tls-cutover-complete
author: Otto Vernal
tags: [devlog, infra, letta]
excerpt: agents.the-last-job is now live over HTTPS and multihost is pointed at the FQDN.
---
Shipped as **`historical entry`**.

## What Changed

This entry documents NPC narration work, including cache behavior and controls that keep AI-backed flavor usable in play.

Infra unblocked and completed.

## What is now live

- DNS A: `agents.the-last-job.decisionsciencecorp.com -> 64.94.85.58`
- TLS certificate issued with certbot on the agent box
- HTTPS health endpoint returns 200:
  - `https://agents.the-last-job.decisionsciencecorp.com/v1/health/`

## Multihost cutover

Updated `/var/www/the-last-job.decisionsciencecorp.com/config/letta.php` to use:

`https://agents.the-last-job.decisionsciencecorp.com`

instead of the raw IP endpoint.

## Runtime hardening

Narrated runs were occasionally returning 504 under slow model responses.
Reduced Letta request timeout in the same config to 10s so `play.php?narrate=1`
fails gracefully inside the page instead of taking the whole request down.

## Why It Matters

AI narration needs operational discipline. The player should get richer texture without waiting on slow live calls or losing deterministic replay behavior.

## Implementation Notes

The commit message for this slice was:

```text
Agents DNS+TLS cutover complete; narrate 504 mitigated.
```

Files touched in this slice included:

- No file list was available for this historical entry.

## Verification

- The entry is part of the devlog archive and renders through the same PHP Markdown pipeline as current posts.
- The test suite now requires every devlog post to carry substantive details, illustration and screenshot figures, and valid visual asset references.
- Current browser smoke checks cover representative rich posts and older auto-generated posts after expansion.

## Visuals

![Devlog illustration](/blog/assets/visuals/illustrations/agents-dns-tls-cutover-complete.svg)

![Devlog screenshot](/blog/assets/visuals/screenshots/job-board.png)
