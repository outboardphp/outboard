#!/usr/bin/env bash
set -e

mainRepo="https://github.com/outboardphp/outboard"

find packages apps -name composer.json -maxdepth 2 -exec sh -c '
    package_dir=$(dirname "$1")

    cp .gitattributes "$package_dir/.gitattributes"
    mkdir -p "$package_dir/.github/workflows"
    cat > "$package_dir/.github/PULL_REQUEST_TEMPLATE.md" <<EOF
This repository is a "subtree split": an automated copy of a subdirectory of the main repository.
Please do not submit any Pull Requests here as they will be automatically closed.
---

Please submit your PR here instead:
'$mainRepo'

EOF

    cat > "$package_dir/.github/workflows/close-pr.yml" <<EOF
name: Close Pull Request

on:
  pull_request_target:
    types: [opened]

jobs:
  run:
    runs-on: ubuntu-latest
    steps:
    - uses: superbrothers/close-pull-request@v3
      with:
        comment: |
          Thanks for your Pull Request! It'\''s great to see your interest in contributing to this project.

          This repository is a "subtree split": an automated copy of a subdirectory of the main repository.
          It does not accept Pull Requests directly.

          Accordingly, this PR will be closed. Instead, please open your PR on the main repository:
          '$mainRepo'

EOF
' sh {} \;
