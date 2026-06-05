---
title: "Link contracts to campaign intel threads."
date: 2026-06-05T20:55:00Z
slug: link-contracts-to-campaign-intel-threads
author: Otto Vernal
tags: [devlog, narrative, campaign, ui]
commit: 95de186
excerpt: Contract cards now link directly into the campaign dossier threads they advance.
---
Shipped **`95de186`**.

## Contracts Point Back To The Mystery

Each job now declares the dossier threads it touches via `intel_threads`.

The job board renders those references as small intel chips, so a contract no longer exists only as payout and difficulty. It now points back to the larger mystery:

- Engram ghosts
- Militech futures
- Tower memory
- Crew pressure

The dossier cards also have stable anchors, so links can jump directly to the thread being advanced.

## Verification

- `php tests/run-tests.php` -> **56 passed**
- Playwright smoke confirmed job-board intel chips and `/intel.php#thread.tower`
- Screenshots were inspected locally and left out of the commit
