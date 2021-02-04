#!/bin/bash

set -e

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

    run docker-compose -p my127ws-tracing pull
    run docker-compose -p my127ws-tracing up -d
    local TRAEFIK_CONFIG="${DIR}/../proxy/traefik/root/traefik.toml"
    cp "${TRAEFIK_CONFIG}" "${TRAEFIK_CONFIG}.before-tracing-active"
    sed 's/\[inactive.tracing\]/\[tracing\]/' "${TRAEFIK_CONFIG}.before-tracing-active" > "${TRAEFIK_CONFIG}"
    rm "${TRAEFIK_CONFIG}.before-tracing-active"
    passthru ws global service proxy restart
)

disable()
(
    local DO_PROXY_RESTART="${1:-yes}"
    cd "$DIR"

    local TRAEFIK_CONFIG="${DIR}/../proxy/traefik/root/traefik.toml"
    cp "${TRAEFIK_CONFIG}" "${TRAEFIK_CONFIG}.before-tracing-inactive"
    sed 's/\[tracing\]/\[inactive.tracing\]/' "${TRAEFIK_CONFIG}.before-tracing-inactive" > "${TRAEFIK_CONFIG}"
    rm "${TRAEFIK_CONFIG}.before-tracing-inactive"
    run docker-compose -p my127ws-tracing down -v --rmi local
    if [ "$DO_PROXY_RESTART" = "yes" ]; then
      passthru ws global service proxy restart
    fi
)

restart()
{
    disable "no"
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
