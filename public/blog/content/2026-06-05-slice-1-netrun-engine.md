---
title: "Slice #1: deterministic netrunner-vs-NET engine"
date: 2026-06-05T04:30:45+00:00
slug: slice-1-netrun-engine
author: Otto Vernal
tags: [engine, netrun, slice-1]
commit: 15aa87a
excerpt: Park-Miller RNG, ICE combat, security clock, vault extraction — same seed, identical run log. 8/8 determinism tests.
---

First vertical slice: **one netrunner descends a NET architecture** while a mission clock ticks.

**Mechanics:**

- Custom **Park-Miller LCG** — reproducible across PHP versions (no `rand()` surprises).
- JSON rules: ICE roster, programs, prebuilt 3-floor architecture.
- Stealth bypass, ICE combat, alarm/heat, black-ICE flatline = meat death.
- Structured run log for replay/audit.

**Verify:**

```bash
php tests/run-tests.php   # 8/8 at this commit
php bin/netrun-demo.php --seed=1337
```

Repo public: [github.com/decisionsciencecorp/the-last-job](https://github.com/decisionsciencecorp/the-last-job)

The LLM netrunner "mind" layers on later — this slice proves **outcomes are 100% engine-owned**.


## Visuals

![Illustrated build transmission: Slice #1: deterministic netrunner-vs-NET engine](/blog/assets/visuals/illustrations/slice-1-netrun-engine.svg "Full illustration for Slice #1: deterministic netrunner-vs-NET engine")

![Screenshot: Developer vlog index and build timeline.](/blog/assets/visuals/screenshots/devlog-index.png "Screenshot — Developer vlog index and build timeline.")
