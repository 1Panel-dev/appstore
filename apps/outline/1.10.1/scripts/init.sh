#!/bin/bash
# Generated from official installation evidence: Outline Dockerfile (uid 1001) and
# Keycloak container image (uid 1000, --import-realm startup import).
set -euo pipefail

umask 077

DATA_DIR="./data"
OUTLINE_DATA_DIR="${DATA_DIR}/outline"
SECRETS_DIR="${DATA_DIR}/secrets"
KEYCLOAK_DATA_DIR="${DATA_DIR}/keycloak"
KEYCLOAK_IMPORT_DIR="${KEYCLOAK_DATA_DIR}/import"
REALM_FILE="${KEYCLOAK_IMPORT_DIR}/outline-realm.json"
REALM_NAME="outline"
CLIENT_ID="outline"

mkdir -p "${OUTLINE_DATA_DIR}" "${SECRETS_DIR}" "${KEYCLOAK_IMPORT_DIR}"
chmod 700 "${DATA_DIR}" "${SECRETS_DIR}"
touch .env
chmod 600 .env

# Preserve attachments from earlier package layouts before switching the Outline
# mount to data/outline. Existing files are never overwritten.
shopt -s nullglob dotglob
for legacy_entry in "${DATA_DIR}"/*; do
    legacy_name="$(basename "${legacy_entry}")"
    case "${legacy_name}" in
        outline|secrets|keycloak|mailpit|pocket-id|*.env|.*)
            continue
            ;;
    esac

    if [[ ! -e "${OUTLINE_DATA_DIR}/${legacy_name}" ]]; then
        mv "${legacy_entry}" "${OUTLINE_DATA_DIR}/"
    fi
done
shopt -u nullglob dotglob

read_env_value() {
    local key="$1"
    if [[ -f .env ]]; then
        sed -n "s/^${key}=//p" .env | tail -n 1 | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
    fi
}

write_secret() {
    local path="$1"
    local value="$2"
    if [[ ! -s "${path}" ]]; then
        printf '%s\n' "${value}" >"${path}"
        chmod 600 "${path}"
    fi
}

outline_url="$(read_env_value PANEL_APP_URL)"
keycloak_port="$(read_env_value PANEL_APP_PORT_HTTP_KEYCLOAK)"
container_name="$(read_env_value CONTAINER_NAME)"
admin_password="$(read_env_value KEYCLOAK_ADMIN_PASSWORD)"

if [[ -z "${outline_url}" ]]; then
    echo "PANEL_APP_URL must be set to the browser-facing Outline URL" >&2
    exit 1
fi
if [[ -z "${keycloak_port}" ]]; then
    echo "PANEL_APP_PORT_HTTP_KEYCLOAK must be set" >&2
    exit 1
fi
if [[ -z "${admin_password}" ]]; then
    echo "KEYCLOAK_ADMIN_PASSWORD must be set" >&2
    exit 1
fi
if [[ -z "${container_name}" ]]; then
    echo "CONTAINER_NAME must be set" >&2
    exit 1
fi
if [[ ! "${admin_password}" =~ ^[A-Za-z0-9._@#%+=:!-]{6,64}$ ]]; then
    echo "KEYCLOAK_ADMIN_PASSWORD must be 6-64 characters using letters, digits or . _ @ # % + = : ! -" >&2
    exit 1
fi

# Derive the Keycloak public URL from the Outline URL: same scheme and host,
# Keycloak port. Both URLs must be reachable from the browser.
outline_url="${outline_url%/}"
scheme="${outline_url%%://*}"
rest="${outline_url#*://}"
authority="${rest%%/*}"
if [[ "${scheme}" == "${outline_url}" || -z "${authority}" ]]; then
    echo "PANEL_APP_URL must include a scheme, e.g. http://192.168.1.10:12115" >&2
    exit 1
fi
case "${authority}" in
    \[*\]*)
        host="${authority%%]*}]"
        ;;
    *)
        host="${authority%%:*}"
        ;;
esac
keycloak_public_url="${scheme}://${host}:${keycloak_port}"

# Address the bundled Keycloak by its unique container name. Compose service
# names are shared across installations on the same Docker network, so using
# "keycloak" as the hostname could reach another Outline instance's Keycloak.
keycloak_internal_url="http://${container_name}-keycloak:8080"

# The OIDC client secret is generated once and reused, so restarts and upgrades
# never invalidate the pairing between Outline and Keycloak.
keycloak_client_secret="$(read_env_value KEYCLOAK_CLIENT_SECRET)"
if [[ -z "${keycloak_client_secret}" ]]; then
    keycloak_client_secret="$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')"
fi
write_secret "${SECRETS_DIR}/keycloak-client-secret" "${keycloak_client_secret}"
keycloak_client_secret="$(cat "${SECRETS_DIR}/keycloak-client-secret")"

# Runtime configuration for the containers lives in dedicated env files so that
# 1Panel parameter updates (which rewrite .env) can never drop the generated
# client secret or the derived Keycloak URL.
cat >"${DATA_DIR}/oidc.env" <<OIDCENV
OIDC_DISPLAY_NAME=Keycloak
OIDC_CLIENT_ID=${CLIENT_ID}
OIDC_CLIENT_SECRET=${keycloak_client_secret}
OIDC_AUTH_URI=${keycloak_public_url}/realms/${REALM_NAME}/protocol/openid-connect/auth
OIDC_TOKEN_URI=${keycloak_internal_url}/realms/${REALM_NAME}/protocol/openid-connect/token
OIDC_USERINFO_URI=${keycloak_internal_url}/realms/${REALM_NAME}/protocol/openid-connect/userinfo
OIDC_LOGOUT_URI=${keycloak_public_url}/realms/${REALM_NAME}/protocol/openid-connect/logout
OIDC_USERNAME_CLAIM=preferred_username
OIDC_SCOPES=openid profile email
OIDCENV
chmod 600 "${DATA_DIR}/oidc.env"

# Keycloak cannot configure the master realm through realm import, so the
# container applies the admin console localization itself after startup. This
# keeps the package to two long-running containers: 1Panel treats exited
# containers as an application error.
cat >"${DATA_DIR}/keycloak-start.sh" <<'STARTSCRIPT'
#!/bin/bash
set -u

/opt/keycloak/bin/kc.sh start-dev --import-realm &
keycloak_pid=$!

trap 'kill -TERM "${keycloak_pid}" 2>/dev/null || true' TERM INT

KCADM=/opt/keycloak/bin/kcadm.sh
CFG=/tmp/kcadm.config
for _ in $(seq 1 60); do
    if "${KCADM}" config credentials --config "${CFG}" \
        --server http://localhost:8080 --realm master \
        --user "${KC_BOOTSTRAP_ADMIN_USERNAME:-admin}" \
        --password "${KC_BOOTSTRAP_ADMIN_PASSWORD:-}" >/dev/null 2>&1; then
        "${KCADM}" update realms/master --config "${CFG}" \
            -s internationalizationEnabled=true \
            -s 'supportedLocales=["zh-CN","en"]' \
            -s defaultLocale=zh-CN >/dev/null 2>&1 || true
        break
    fi
    sleep 3
done

wait "${keycloak_pid}"
STARTSCRIPT
chmod 700 "${DATA_DIR}/keycloak-start.sh"

cat >"${DATA_DIR}/keycloak.env" <<KCENV
KC_BOOTSTRAP_ADMIN_USERNAME=admin
KC_BOOTSTRAP_ADMIN_PASSWORD=${admin_password}
KC_HTTP_ENABLED=true
KC_HOSTNAME=${keycloak_public_url}
KC_HOSTNAME_STRICT=false
KC_PROXY_HEADERS=xforwarded
KCENV
chmod 600 "${DATA_DIR}/keycloak.env"

outline_secret_key="$(read_env_value SECRET_KEY)"
if [[ ! "${outline_secret_key}" =~ ^[0-9a-fA-F]{64}$ ]]; then
    outline_secret_key="$(od -An -N32 -tx1 /dev/urandom | tr -d ' \n')"
fi
write_secret "${SECRETS_DIR}/outline-secret-key" "${outline_secret_key}"

outline_utils_secret="$(read_env_value UTILS_SECRET)"
if [[ -z "${outline_utils_secret}" ]]; then
    outline_utils_secret="$(od -An -N32 -tx1 /dev/urandom | tr -d ' \n')"
fi
write_secret "${SECRETS_DIR}/outline-utils-secret" "${outline_utils_secret}"

# Rebuild the realm import every run so that the 1Panel parameters stay the
# single source of truth: the redirect URL follows PANEL_APP_URL and the initial
# password follows KEYCLOAK_ADMIN_PASSWORD.
cat >"${REALM_FILE}" <<REALM
{
  "realm": "${REALM_NAME}",
  "enabled": true,
  "sslRequired": "none",
  "registrationAllowed": false,
  "loginWithEmailAllowed": true,
  "internationalizationEnabled": true,
  "supportedLocales": ["zh-CN", "en"],
  "defaultLocale": "zh-CN",
  "duplicateEmailsAllowed": false,
  "resetPasswordAllowed": true,
  "editUsernameAllowed": false,
  "clients": [
    {
      "clientId": "${CLIENT_ID}",
      "name": "Outline",
      "enabled": true,
      "protocol": "openid-connect",
      "publicClient": false,
      "bearerOnly": false,
      "standardFlowEnabled": true,
      "implicitFlowEnabled": false,
      "directAccessGrantsEnabled": false,
      "serviceAccountsEnabled": false,
      "secret": "${keycloak_client_secret}",
      "redirectUris": [
        "${outline_url}/auth/oidc.callback"
      ],
      "webOrigins": [
        "${outline_url}"
      ],
      "attributes": {
        "post.logout.redirect.uris": "${outline_url}/*"
      }
    }
  ],
  "users": [
    {
      "username": "admin",
      "enabled": true,
      "emailVerified": true,
      "email": "admin@outline.local",
      "firstName": "Outline",
      "lastName": "Admin",
      "credentials": [
        {
          "type": "password",
          "value": "${admin_password}",
          "temporary": false
        }
      ]
    }
  ]
}
REALM
chmod 600 "${REALM_FILE}"

# The official images run as fixed non-root users that need writable data.
chown -R 1001:1001 "${OUTLINE_DATA_DIR}"
chown -R 1000:1000 "${KEYCLOAK_DATA_DIR}"
chown 1000:1000 "${DATA_DIR}/keycloak-start.sh"
chown 1001:1001 "${SECRETS_DIR}/outline-secret-key" "${SECRETS_DIR}/outline-utils-secret"
chmod 600 "${SECRETS_DIR}"/*
chmod 750 "${OUTLINE_DATA_DIR}"
chmod 750 "${KEYCLOAK_DATA_DIR}"
