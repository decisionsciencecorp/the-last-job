# The Last Job agents host rebuild runbook

This runbook captures the exact setup flavor we used for the Letta agents host so we can recreate it after the pause.

## What this host was

- Purpose: dedicated Letta API host for NPC narration backend.
- Public FQDN: `agents.the-last-job.decisionsciencecorp.com`
- Historical A record target: `64.94.85.58`
- Letta health endpoint used: `https://agents.the-last-job.decisionsciencecorp.com/v1/health/`
- Prior direct-IP health before DNS/TLS cutover: `http://64.94.85.58:8283/v1/health/`

## Architecture flavor (exact pattern)

- Single VM running Letta HTTP API (port `8283`) behind nginx.
- TLS terminated on nginx with certbot-managed certificate.
- Game app on multihost points to the FQDN over HTTPS, not raw IP.
- Letta request timeout on game host intentionally reduced to `10s` to fail gracefully on slow model responses.

## Game host integration points

### Multihost config file

- File path used in production:
  - `/var/www/the-last-job.decisionsciencecorp.com/config/letta.php`
- Base URL set to:
  - `https://agents.the-last-job.decisionsciencecorp.com`
- Timeout hardening:
  - request timeout set to `10s` in the same config.

### App-level loader behavior (repo-confirmed)

- Config discovery order in `public/includes/Letta/LettaServices.php`:
  1. `public/includes/config.letta.local.php` (local override)
  2. `{DB_PARENT}/config/letta.php` (multihost production path)
  3. env vars fallback:
     - `LASTJOB_LETTA_BASE_URL`
     - `LASTJOB_LETTA_API_KEY`
     - `LASTJOB_LETTA_AGENT_ID`
     - optional `LASTJOB_LETTA_CACHE_DB`

- Example config in repo (`public/includes/config.letta.example.php`) uses:
  - `base_url`: `https://agents.the-last-job.decisionsciencecorp.com`
  - `agent_id`: `agent-8c6d5b9a-a05b-4a95-b2e0-13b15972e31e`
  - `cache_db`: `/var/www/the-last-job.decisionsciencecorp.com/db/npc-intent.sqlite`

## Rebuild checklist

1. Provision new VM for agents host.
2. Install and run Letta server on the VM (same API family used previously: `/v1/...` endpoints).
3. Verify local Letta health on VM via direct API port.
4. Configure nginx reverse proxy for `agents.the-last-job.decisionsciencecorp.com` -> Letta API upstream.
5. Issue TLS cert with certbot for the FQDN.
6. Verify public HTTPS health:
   - `https://agents.the-last-job.decisionsciencecorp.com/v1/health/` returns 200.
7. On multihost, update `config/letta.php` base URL to the agents FQDN.
8. Keep request timeout at `10s` unless there is an explicit reason to change it.
9. Validate in app:
   - `play.php?narrate=1` fails gracefully if upstream is slow.
   - no hard dependency on direct IP.

## Deprovision checklist

1. Remove/destroy the agents VM in provider panel.
2. Remove or park DNS record for `agents.the-last-job.decisionsciencecorp.com`.
3. Revoke/rotate host-local secrets that were specific to that VM.
4. Keep this runbook and the game host config pattern for later reprovision.

## Current pause note

- During this pause, deprovision is requested and should be executed through Ada's VPS lifecycle lane.
- If deprovision is done while this repo remains unchanged, the game should still degrade gracefully when Letta is unavailable (as designed by timeout and error handling paths).
