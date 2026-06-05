#!/usr/bin/env python3
"""Crawl internal anchors/assets and fail on broken links.

Usage:
    python3 tools/e2e-link-check.py http://127.0.0.1:8099/
    python3 tools/e2e-link-check.py https://the-last-job.decisionsciencecorp.com/ https://dev.the-last-job.decisionsciencecorp.com/
"""

from __future__ import annotations

import argparse
from collections import deque
from html.parser import HTMLParser
from typing import Iterable
from urllib.error import HTTPError
from urllib.parse import urldefrag, urljoin, urlparse
from urllib.request import Request, urlopen


class LinkParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.links: list[str] = []
        self.assets: list[str] = []
        self.forms: list[str] = []

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        data = {k: v for k, v in attrs if v is not None}
        if tag == "a" and data.get("href"):
            self.links.append(data["href"])
        elif tag == "img" and data.get("src"):
            self.assets.append(data["src"])
        elif tag == "link" and data.get("href"):
            self.assets.append(data["href"])
        elif tag == "script" and data.get("src"):
            self.assets.append(data["src"])
        elif tag == "form":
            self.forms.append(data.get("action", ""))


def fetch(url: str, timeout: int) -> tuple[int | None, str, str, str]:
    request = Request(url, headers={"User-Agent": "TheLastJob-e2e-link-check/1.0"})
    try:
        with urlopen(request, timeout=timeout) as response:
            body = response.read(800_000).decode("utf-8", "replace")
            return response.status, response.geturl(), response.headers.get("content-type", ""), body
    except HTTPError as error:
        return error.code, url, error.headers.get("content-type", ""), str(error)
    except Exception as error:  # noqa: BLE001 - CLI should report every failure shape.
        return None, url, "", repr(error)


def same_host(url: str, host: str) -> bool:
    parsed = urlparse(url)
    return parsed.netloc == host


def should_skip(url: str) -> bool:
    parsed = urlparse(url)
    return parsed.scheme in {"mailto", "tel", "javascript"} or parsed.path == ""


def normalize(base: str, href: str) -> str:
    return urldefrag(urljoin(base, href))[0]


def crawl(start: str, max_pages: int, timeout: int) -> list[str]:
    host = urlparse(start).netloc
    queue: deque[str] = deque([start])
    seen_pages: set[str] = set()
    checked_assets: set[str] = set()
    failures: list[str] = []

    while queue and len(seen_pages) < max_pages:
        url = urldefrag(queue.popleft())[0]
        if url in seen_pages or should_skip(url):
            continue
        seen_pages.add(url)

        status, final_url, content_type, body = fetch(url, timeout)
        if status is None or status >= 400:
            failures.append(f"page {status or 'ERR'} {url}")
            continue
        if "text/html" not in content_type:
            continue

        parser = LinkParser()
        parser.feed(body)

        for href in parser.links:
            next_url = normalize(final_url, href)
            if same_host(next_url, host) and next_url not in seen_pages:
                queue.append(next_url)

        for action in parser.forms:
            if not action:
                continue
            action_url = normalize(final_url, action)
            if same_host(action_url, host) and action_url not in seen_pages:
                queue.append(action_url)

        for src in parser.assets:
            asset_url = normalize(final_url, src)
            if not same_host(asset_url, host) or asset_url in checked_assets:
                continue
            checked_assets.add(asset_url)
            asset_status, _, _, _ = fetch(asset_url, timeout)
            if asset_status is None or asset_status >= 400:
                failures.append(f"asset {asset_status or 'ERR'} {asset_url} referenced from {url}")

    print(f"{start}: crawled {len(seen_pages)} pages, checked {len(checked_assets)} assets")
    return failures


def main(argv: Iterable[str] | None = None) -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url", nargs="+")
    parser.add_argument("--max-pages", type=int, default=120)
    parser.add_argument("--timeout", type=int, default=20)
    args = parser.parse_args(argv)

    failures: list[str] = []
    for base_url in args.base_url:
        failures.extend(crawl(base_url, args.max_pages, args.timeout))

    if failures:
        print("\nBroken internal links/assets:")
        for failure in failures:
            print(f"- {failure}")
        return 1

    print("\nNo broken internal links/assets found.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
