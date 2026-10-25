#!/usr/bin/env python3
"""Build the static site published by GitHub Pages."""

from __future__ import annotations

import json
import shutil
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "dist"
STATIC_PATHS = ("index.html", "assets", "css", "js")


def build() -> None:
    OUTPUT.mkdir(parents=True, exist_ok=True)

    for relative_path in STATIC_PATHS:
        source = ROOT / relative_path
        destination = OUTPUT / relative_path
        if source.is_dir():
            shutil.copytree(source, destination, dirs_exist_ok=True)
        elif source.is_file():
            shutil.copy2(source, destination)
        else:
            raise FileNotFoundError(f"Required static site path not found: {source}")

    index_path = OUTPUT / "index.html"
    index_html = index_path.read_text(encoding="utf-8")
    runtime_marker = '<meta name="portfolio-runtime" content="auto">'
    if runtime_marker not in index_html:
        raise ValueError("index.html is missing the portfolio runtime marker")
    index_path.write_text(
        index_html.replace(runtime_marker, '<meta name="portfolio-runtime" content="static">', 1),
        encoding="utf-8",
    )

    sys.path.insert(0, str(ROOT / "backend_py"))
    from stats_service import analizar_workspace

    stats = analizar_workspace(str(ROOT))
    stats["source"] = "build"
    stats["runtime"].pop("workspacePath", None)

    stats_path = OUTPUT / "assets" / "data" / "portfolio-stats.json"
    stats_path.write_text(
        json.dumps(stats, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    (OUTPUT / ".nojekyll").touch()
    print(f"Static site built successfully at {OUTPUT}")


if __name__ == "__main__":
    build()
