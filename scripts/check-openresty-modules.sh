#!/usr/bin/env bash
# Verify that every OpenResty version keeps build/module.json and
# build/module.catalog.json byte-identical (the two files must stay in sync).
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
failed=0

for module_json in "${root_dir}"/apps/openresty/*/build/module.json; do
    [[ -f "${module_json}" ]] || continue
    catalog_json="$(dirname "${module_json}")/module.catalog.json"
    # Only versions carrying the dynamic-module catalog need the sync check.
    [[ -f "${catalog_json}" ]] || continue
    if ! diff -u "${module_json}" "${catalog_json}"; then
        echo "MISMATCH: ${module_json} and ${catalog_json} differ"
        failed=1
    fi
done

if [[ "${failed}" -ne 0 ]]; then
    echo "OpenResty module.json/module.catalog.json consistency check failed"
    exit 1
fi
echo "OpenResty module.json/module.catalog.json consistency check passed"
