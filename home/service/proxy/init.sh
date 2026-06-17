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
        MY127WS_PROXY_DOMAIN="${MY127WS_PROXY_DOMAIN:-$(ws global config get global.service.proxy.domain)}"
        MY127WS_PROXY_HTTPS_CRT="${MY127WS_PROXY_HTTPS_CRT:-$(ws global config get global.service.proxy.https.crt)}"
        MY127WS_PROXY_HTTPS_KEY="${MY127WS_PROXY_HTTPS_KEY:-$(ws global config get global.service.proxy.https.key)}"
        MY127WS_PROXY_HTTPS_CRT_FILE="${MY127WS_PROXY_HTTPS_CRT_FILE:-$(ws global config get global.service.proxy.https.crt_file)}"
        MY127WS_PROXY_HTTPS_KEY_FILE="${MY127WS_PROXY_HTTPS_KEY_FILE:-$(ws global config get global.service.proxy.https.key_file)}"
        export MY127WS_PROXY_DOMAIN

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
