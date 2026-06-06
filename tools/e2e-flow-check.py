#!/usr/bin/env python3
"""Exercise player-facing flows that link crawling cannot cover.

Usage:
    python3 tools/e2e-flow-check.py http://127.0.0.1:8099/
    python3 tools/e2e-flow-check.py https://the-last-job.decisionsciencecorp.com/
"""

from __future__ import annotations

import argparse
import http.cookiejar
import json
import time
from dataclasses import dataclass, field
from html.parser import HTMLParser
from typing import Iterable
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode, urljoin, urlparse
from urllib.request import HTTPCookieProcessor, Request, build_opener, urlopen


@dataclass
class Control:
    tag: str
    attrs: dict[str, str]
    options: list[dict[str, str]] = field(default_factory=list)


@dataclass
class Form:
    action: str
    method: str
    controls: list[Control] = field(default_factory=list)


class SurfaceParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.forms: list[Form] = []
        self.prefetch_urls: list[str] = []
        self._form: Form | None = None
        self._select: Control | None = None

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        data = {k: v or "" for k, v in attrs}
        if tag == "form":
            self._form = Form(
                action=data.get("action", ""),
                method=data.get("method", "get").lower(),
            )
            self.forms.append(self._form)
            return
        if tag == "button" and data.get("data-prefetch-url"):
            self.prefetch_urls.append(data["data-prefetch-url"])
        if self._form is None:
            return
        if tag in {"input", "button"}:
            self._form.controls.append(Control(tag, data))
        elif tag == "select":
            self._select = Control(tag, data)
            self._form.controls.append(self._select)
        elif tag == "option" and self._select is not None:
            self._select.options.append(data)

    def handle_endtag(self, tag: str) -> None:
        if tag == "form":
            self._form = None
        elif tag == "select":
            self._select = None


def fetch(url: str, timeout: int = 30, attempts: int = 3) -> tuple[int, str, str, str]:
    request = Request(url, headers={"User-Agent": "TheLastJob-e2e-flow-check/1.0"})
    last_error: Exception | None = None
    for attempt in range(1, attempts + 1):
        try:
            with urlopen(request, timeout=timeout) as response:
                return (
                    response.status,
                    response.geturl(),
                    response.headers.get("content-type", ""),
                    response.read(1_200_000).decode("utf-8", "replace"),
                )
        except HTTPError as error:
            return (
                error.code,
                url,
                error.headers.get("content-type", ""),
                error.read(1_200_000).decode("utf-8", "replace"),
            )
        except (TimeoutError, URLError, OSError) as error:
            last_error = error
            if attempt < attempts:
                time.sleep(1.5 * attempt)

    return 0, url, "", repr(last_error)


def assert_page(url: str, *, must_contain: str | None = None, allow_status: set[int] | None = None) -> str:
    status, _, content_type, body = fetch(url)
    allowed = allow_status or {200}
    if status not in allowed:
        raise AssertionError(f"{url} returned HTTP {status}")
    if status == 200 and "text/html" in content_type:
        bad_needles = ["Fatal error", "Parse error", "Warning:", "Uncaught"]
        for needle in bad_needles:
            if needle in body:
                raise AssertionError(f"{url} rendered PHP error marker: {needle}")
    if must_contain is not None and must_contain not in body:
        raise AssertionError(f"{url} did not contain expected text: {must_contain!r}")
    return body


def form_payload(form: Form) -> list[tuple[str, str]]:
    payload: list[tuple[str, str]] = []
    radio_seen: dict[str, str] = {}
    radio_checked: dict[str, str] = {}
    submit: tuple[str, str] | None = None

    for control in form.controls:
        attrs = control.attrs
        name = attrs.get("name")
        if not name or "disabled" in attrs:
            continue
        ctype = attrs.get("type", control.tag).lower()
        if control.tag == "select":
            selected = next((o for o in control.options if "selected" in o), None)
            selected = selected or (control.options[0] if control.options else {})
            payload.append((name, selected.get("value", "")))
        elif ctype == "radio":
            radio_seen.setdefault(name, attrs.get("value", "on"))
            if "checked" in attrs:
                radio_checked[name] = attrs.get("value", "on")
        elif ctype == "checkbox":
            if "checked" in attrs:
                payload.append((name, attrs.get("value", "on")))
        elif ctype == "submit":
            if submit is None:
                submit = (name, attrs.get("value", ""))
        elif ctype != "button":
            payload.append((name, attrs.get("value", "")))

    for name, value in radio_seen.items():
        payload.append((name, radio_checked.get(name, value)))
    if submit is not None:
        payload.append(submit)
    return payload


def submit_forms(base: str, path: str) -> list[str]:
    url = urljoin(base, path)
    body = assert_page(url)
    parser = SurfaceParser()
    parser.feed(body)
    submitted: list[str] = []
    for idx, form in enumerate(parser.forms, start=1):
        if form.method != "get":
            raise AssertionError(f"{url} form #{idx} uses unsupported method {form.method}")
        target = urljoin(url, form.action or path)
        separator = "&" if urlparse(target).query else "?"
        submit_url = target + separator + urlencode(form_payload(form), doseq=True)
        assert_page(submit_url)
        submitted.append(submit_url)
    return submitted


def assert_json(url: str, expected_status: set[int]) -> None:
    status, _, content_type, body = fetch(url)
    if status not in expected_status:
        raise AssertionError(f"{url} returned HTTP {status}; expected one of {sorted(expected_status)}")
    if "application/json" not in content_type:
        raise AssertionError(f"{url} did not return JSON content-type: {content_type}")
    try:
        json.loads(body)
    except json.JSONDecodeError as exc:
        raise AssertionError(f"{url} returned invalid JSON: {exc}") from exc


def assert_terminal_sequence(base: str) -> None:
    jar = http.cookiejar.CookieJar()
    opener = build_opener(HTTPCookieProcessor(jar))
    endpoint = urljoin(base, "api/terminal-command.php")
    expectations = [
        ("answer", "ANIMAL"),
        ("I learned in a corpo office", "who still has a piece of you"),
        ("My sister still owns me", "what do people pay you for"),
        ("I get people out clean", "what don't you do"),
        ("I don't sell kids", "bring kojo"),
        ("bring kojo", "NCART Empty Cage"),
        ("list contracts", "contract packets"),
        ("inspect contract 1", "contract.packet"),
        ("accept", "run contract 1"),
        ("run contract 1", "--- run"),
        ("wake", "wake.after_action"),
        ("file", "shard.ncart.empty-cage"),
        ("answer next call", "package was empty"),
    ]
    for command, expected in expectations:
        request = Request(
            endpoint,
            data=json.dumps({"command": command}).encode("utf-8"),
            headers={
                "Content-Type": "application/json",
                "User-Agent": "TheLastJob-e2e-flow-check/1.0",
            },
        )
        with opener.open(request, timeout=30) as response:
            body = response.read(1_200_000).decode("utf-8", "replace")
            if response.status != 200:
                raise AssertionError(f"{endpoint} returned HTTP {response.status} for {command!r}")
        try:
            payload = json.loads(body)
        except json.JSONDecodeError as exc:
            raise AssertionError(f"{endpoint} returned invalid JSON for {command!r}: {exc}") from exc
        if payload.get("status") != "ok":
            raise AssertionError(f"{endpoint} returned non-ok payload for {command!r}: {payload!r}")
        lines = "\n".join(str(line) for line in payload.get("lines", []))
        if expected not in lines:
            raise AssertionError(f"{command!r} did not produce expected terminal output {expected!r}")


def run(base: str) -> None:
    base = base.rstrip("/") + "/"

    checks = [
        ("", "The Last Job terminal application"),
        ("", "Terminal scrollback"),
        ("", "rule: no crew contact until the fixer makes the intro"),
        ("crew.php", "crew through the fixer"),
        ("crew.php", "Fixer dialogue"),
        ("play.php", "contract packets"),
        ("play.php?wire=call", "Fixer on the line"),
        ("play.php?wire=ring", "line dropped"),
        ("intel.php", "Active shards"),
        ("blog/", "Devlog"),
        ("blog/?slug=how-to-approach-the-current-build", "Suggested Playtest Route"),
    ]
    for path, expected in checks:
        assert_page(urljoin(base, path), must_contain=expected)

    home_body = assert_page(base)
    forbidden_home_terms = ["<nav", "Job board", "Build your crew", "Pick a contract"]
    for term in forbidden_home_terms:
        if term in home_body:
            raise AssertionError(f"/ still exposes old website workflow term: {term!r}")
    assert_terminal_sequence(base)

    submitted = []
    submitted.extend(submit_forms(base, "crew.php"))
    submitted.extend(submit_forms(base, "play.php"))

    explicit_html_flows = [
        ("crew.php?roll=1&seed=3111&campaign=1&street_cred=4&role0=role.tech&role1=role.tech&role2=role.solo&role3=role.netrunner", "crew through the fixer"),
        ("crew.php?roll=1&seed=3111&campaign=1&street_cred=4&role0=role.solo&role1=role.netrunner&role2=role.tech&role3=role.fixer&chrome0%5B%5D=cw.neural.neurallink&chrome0%5B%5D=cw.optics.targeting", "Install log"),
        ("play.php?seed=2077&campaign=1&street_cred=4&role0=role.solo&role1=role.netrunner&role2=role.tech&role3=role.fixer&run=1&job=job.arasaka-substation", "Mission clock"),
        ("play.php?reset_campaign=1&campaign=1&run=1&job=job.arasaka-substation", "wallet / timeline readout"),
        ("play.php?campaign=0&street_cred=0&run=1&job=job.the-last-job", "Street cred 0 is too low"),
        ("play.php?run=1&job=job.nope", "Unknown contract"),
        ("play.php?job=job.nope", "Unknown contract"),
        ("play.php?campaign=0&street_cred=99&run=1&job=job.pawnshop", "Mission clock"),
        ("play.php?campaign=0&street_cred=99&run=1&job=job.militech-datafort", "Mission clock"),
        ("play.php?campaign=0&street_cred=99&run=1&job=job.the-last-job", "Mission clock"),
    ]
    for path, expected in explicit_html_flows:
        assert_page(urljoin(base, path), must_contain=expected)

    play_body = assert_page(urljoin(base, "play.php"))
    forbidden_wire_terms = ["Fast narrate mode", "Warm narration cache", "campaign (session)", "Job board", "Pick a contract", "Engine online"]
    for term in forbidden_wire_terms:
        if term in play_body:
            raise AssertionError(f"play.php still exposes old player-facing term: {term!r}")
    crew_body = assert_page(urljoin(base, "crew.php"))
    if "mechanical readout" not in crew_body:
        raise AssertionError("crew.php no longer exposes mechanical readout as a secondary drawer")
    parser = SurfaceParser()
    parser.feed(play_body)
    if not parser.prefetch_urls:
        raise AssertionError("play.php did not expose a narration prefetch button")
    for prefetch in parser.prefetch_urls:
        # Local/dev hosts may not have Letta configured, but the button must return
        # structured JSON, not a 500 HTML error.
        assert_json(urljoin(base, prefetch), {200, 503})

    assert_json(urljoin(base, "api/narrate-prefetch.php?seed=2077&job=job.nope&street_cred=4&max_live=0"), {400})
    assert_json(urljoin(base, "api/narrate-prefetch.php?seed=2077&job=job.the-last-job&street_cred=0&max_live=0"), {403})

    print(f"{base}: {len(checks)} pages, {len(submitted)} form submissions, {len(explicit_html_flows)} explicit flows, {len(parser.prefetch_urls) + 2} JSON button/API flows passed.")


def main(argv: Iterable[str] | None = None) -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url", nargs="+")
    args = parser.parse_args(argv)
    for base in args.base_url:
        run(base)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
