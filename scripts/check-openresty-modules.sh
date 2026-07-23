#!/usr/bin/env bash
# Verify that OpenResty module catalogs contain immutable built-in definitions.
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
failed=0
catalog_count=0

for catalog_json in "${root_dir}"/apps/openresty/*/build/module.catalog.json; do
    [[ -f "${catalog_json}" ]] || continue
    catalog_count=$((catalog_count + 1))
    app_dir="$(dirname "$(dirname "${catalog_json}")")"
    version="$(basename "${app_dir}")"
    module_json="$(dirname "${catalog_json}")/module.json"
    if [[ -e "${module_json}" ]]; then
        echo "INVALID: packaged state file must not exist: ${module_json}"
        failed=1
    fi
    if ! jq -e '
        type == "array" and
        ([.[].name] | length == (unique | length)) and
        all(.[];
            (.name | type == "string" and length > 0) and
            (.script | type == "string") and
            (.packages | type == "array" and all(.[]; type == "string")) and
            (.params | type == "string") and
            (.buildMode == "dynamic" or .buildMode == "static") and
            (.provider | type == "string" and length > 0) and
            (.loadOrder | type == "number" and floor == .) and
            (has("enable") | not) and
            (has("dynamicSupport") | not) and
            (has("custom") | not) and
            (has("builds") | not) and
            (has("lastError") | not) and
            (has("deleted") | not)
        )
    ' "${catalog_json}" >/dev/null; then
        echo "INVALID: ${catalog_json}"
        failed=1
    fi
    if ! grep -Fq "PANEL_OPENRESTY_VERSION=${version}" "${app_dir}/docker-compose.yml" ||
        ! grep -Fq "image: 1panel/openresty:${version}" "${app_dir}/docker-compose.yml" ||
        ! grep -Fq "ARG PANEL_OPENRESTY_VERSION=\"${version}\"" "${app_dir}/build/Dockerfile.modules"; then
        echo "INVALID: OpenResty image version does not match ${version}"
        failed=1
    fi
    if ! grep -Fq 'include /usr/local/openresty/nginx/conf/modules-enabled/*.conf;' "${app_dir}/conf/nginx.conf"; then
        echo "INVALID: dynamic module include is missing from ${app_dir}/conf/nginx.conf"
        failed=1
    fi
done

if [[ "${catalog_count}" -eq 0 ]]; then
    echo "No OpenResty module catalogs found"
    exit 1
fi
if [[ "${failed}" -ne 0 ]]; then
    echo "OpenResty module catalog validation failed"
    exit 1
fi
echo "OpenResty module catalog validation passed"
