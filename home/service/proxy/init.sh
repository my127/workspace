#!/bin/bash

set -o errexit
set -o nounset
set -o pipefail

DIR=""

main()
{
    if [ "$1" = "enable" ]; then
        enable
        exit
    fi

    if [ "$1" = "disable" ]; then
        disable
        exit
    fi

    if [ "$1" = "restart" ]; then
        restart
        exit
    fi
}

enable()
(
    cd "$DIR"

    if ! docker ps | grep my127ws-proxy > /dev/null; then
        local PROXY_ENV_ERROR_MESSAGE="is required. Use ws global service proxy enable or ws global service proxy restart."

        : "${MY127WS_PROXY_HTTPS_CRT:?$PROXY_ENV_ERROR_MESSAGE}"
        : "${MY127WS_PROXY_HTTPS_KEY:?$PROXY_ENV_ERROR_MESSAGE}"
        : "${MY127WS_PROXY_HTTPS_CRT_FILE:?$PROXY_ENV_ERROR_MESSAGE}"
        : "${MY127WS_PROXY_HTTPS_KEY_FILE:?$PROXY_ENV_ERROR_MESSAGE}"

        validate_tls_filenames "$MY127WS_PROXY_HTTPS_CRT_FILE" "$MY127WS_PROXY_HTTPS_KEY_FILE"

        run mkdir -p traefik/root/tls traefik/root/config

        run curl --fail --location --output "traefik/root/tls/${MY127WS_PROXY_HTTPS_CRT_FILE}" "${MY127WS_PROXY_HTTPS_CRT}"
        run curl --fail --location --output "traefik/root/tls/${MY127WS_PROXY_HTTPS_KEY_FILE}" "${MY127WS_PROXY_HTTPS_KEY}"
        write_tls_config "${MY127WS_PROXY_HTTPS_CRT_FILE}" "${MY127WS_PROXY_HTTPS_KEY_FILE}"
        run docker-compose -p my127ws-proxy up --force-recreate --build -d traefik
    fi
)

disable()
(
    cd "$DIR"

    if docker ps | grep my127ws-proxy > /dev/null; then
        run docker-compose -p my127ws-proxy rm --stop --force traefik
    fi
)

restart()
{
    disable
    enable
}

validate_tls_filenames()
{
    validate_tls_filename "$1"
    validate_tls_filename "$2"

    if [ "$1" = "$2" ]; then
        echo "TLS certificate and key filenames must be different." >&2
        exit 1
    fi
}

validate_tls_filename()
{
    case "$1" in
        ""|.|..|*/*)
            echo "Invalid TLS filename: $1" >&2
            exit 1
            ;;
        *)
            ;;
    esac
}

write_tls_config()
{
    cat > traefik/root/config/tls.yaml <<EOF
tls:
  stores:
    default:
      defaultCertificate:
        certFile: /tls/$1
        keyFile: /tls/$2
EOF
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=../../lib/sidekick.sh
    source "$DIR/../../lib/sidekick.sh"
}

bootstrap
main "$@"
