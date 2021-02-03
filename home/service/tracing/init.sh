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
    run cp -pR "$DIR/traefik/root/config/tracing.toml" "$DIR/../proxy/traefik/root/config/"
    passthru ws global service proxy restart
)

disable()
(
    local SKIP_RESTART="${1:-}"

    cd "$DIR"

    if [ -f "$DIR/../proxy/traefik/root/config/tracing.toml" ]; then
      run rm -f "$DIR/../proxy/traefik/root/config/tracing.toml"
    fi
    run docker-compose -p my127ws-tracing down -v --rmi local
    if [ "$SKIP_RESTART" != "skip" ]; then
      passthru ws global service proxy restart
    fi
)

restart()
{
    disable "skip"
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
