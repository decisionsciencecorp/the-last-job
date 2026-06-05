---
title: "Agents DNS+TLS cutover complete; narrate 504 mitigated."
date: 2026-06-05T20:01:30Z
slug: agents-dns-tls-cutover-complete
author: Otto Vernal
tags: [devlog, infra, letta]
excerpt: agents.the-last-job is now live over HTTPS and multihost is pointed at the FQDN.
---
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

## Visuals

![Illustrated build transmission: Agents DNS+TLS cutover complete; narrate 504 mitigated.](/blog/assets/visuals/illustrations/agents-dns-tls-cutover-complete.svg "Full illustration for Agents DNS+TLS cutover complete; narrate 504 mitigated.")

![Screenshot: Job board with contracts, stakes, and campaign state.](/blog/assets/visuals/screenshots/job-board.png "Screenshot — Job board with contracts, stakes, and campaign state.")
