#!/bin/bash

set -e

DIR=""

main()
{
    if [ "$1" = "enable" ] || [ "$1" = "start" ]; then
        start
        exit
    fi

    if [ "$1" = "disable" ] || [ "$1" = "stop" ]; then
        stop
        exit
    fi

    if [ "$1" = "restart" ]; then
        restart
        exit
    fi
}

start()
(
    cd "$DIR"

    run docker-compose -p my127ws-tracing pull
    run docker-compose -p my127ws-tracing up -d

    local TRAEFIK_CONFIG="${DIR}/../proxy/traefik/root/traefik.toml"
    if grep -q '\[inactive.tracing\]' "${TRAEFIK_CONFIG}"; then
      cp "${TRAEFIK_CONFIG}" "${TRAEFIK_CONFIG}.before-tracing-active"
      sed 's/\[inactive.tracing\]/\[tracing\]/' "${TRAEFIK_CONFIG}.before-tracing-active" > "${TRAEFIK_CONFIG}"
      rm "${TRAEFIK_CONFIG}.before-tracing-active"
      passthru ws global service proxy restart
    fi
)

stop()
(
    local DO_PROXY_RESTART="${1:-yes}"
    cd "$DIR"

    local TRAEFIK_CONFIG="${DIR}/../proxy/traefik/root/traefik.toml"
    if [ "$DO_PROXY_RESTART" = "yes" ] && grep -q '\[tracing\]' "${TRAEFIK_CONFIG}"; then
      cp "${TRAEFIK_CONFIG}" "${TRAEFIK_CONFIG}.before-tracing-inactive"
      sed 's/\[tracing\]/\[inactive.tracing\]/' "${TRAEFIK_CONFIG}.before-tracing-inactive" > "${TRAEFIK_CONFIG}"
      rm "${TRAEFIK_CONFIG}.before-tracing-inactive"
      if [ "$DO_PROXY_RESTART" = "yes" ]; then
        passthru ws global service proxy restart
      fi
    fi
    run docker-compose -p my127ws-tracing down -v --rmi local
)

restart()
{
    stop "no"
    start
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=../../lib/sidekick.sh
    source "$DIR/../../lib/sidekick.sh"
}

bootstrap
main "$@"
