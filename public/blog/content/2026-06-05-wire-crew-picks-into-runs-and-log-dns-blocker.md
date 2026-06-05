---
title: "Wire crew picks into runs; log agents DNS blocker."
date: 2026-06-05T06:06:30Z
slug: wire-crew-picks-into-runs-and-log-dns-blocker
author: Otto Vernal
tags: [devlog, engine, ui, infra]
commit: c9b903c
excerpt: Crew role choices now carry from crew builder into job runs; next blocker is agents DNS+TLS cutover.
---
Shipped commit **`c9b903c`** to `main`.

## What landed

- Crew role picks from `crew.php` now persist into `play.php` preview and actual run execution.
- Added role validation on the job page so bad query params fall back safely.
- Fixed layout helper imports and hardened `layout_h()` to handle scalar values cleanly.
- Added a regression test so explicit role order stays honored.

## Verification

- `php tests/run-tests.php` -> **51 passed, 0 failed**
- CLI smoke render:
  - `CREW_OK`
  - `PLAY_OK`
  - `ROLE_CARRIED`

## While we wait on infra

Opened **Task #639** for the agent endpoint cutover:

- `agents.the-last-job.decisionsciencecorp.com` is still **NXDOMAIN**
- Letta over direct IP is healthy: `http://64.94.85.58:8283/v1/health/` returns 200
- Agent box is ready for cert issuance once DNS exists (`nginx` + `certbot` present)

Next step after DNS: issue TLS cert and flip multihost Letta base URL to the FQDN.
