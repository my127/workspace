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
    local TRAEFIK_PROXY_RULE

    cd "$DIR"

    if ! docker ps | grep my127ws-proxy > /dev/null; then

        if [ ! -d "traefik/root/tls" ]; then
            run mkdir -p traefik/root/tls
        fi

        TRAEFIK_PROXY_RULE="$(ws global service proxy config rule proxy)"
        updateEnvGeneratedKey ".env" "TRAEFIK_PROXY_RULE" "$TRAEFIK_PROXY_RULE"
        run ws global service proxy config certificates download traefik/root/tls
        run bash -c 'ws global service proxy config tls > traefik/root/config/tls.yaml'
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

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=../../lib/sidekick.sh
    source "$DIR/../../lib/sidekick.sh"
}

bootstrap
main "$@"
