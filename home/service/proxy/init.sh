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
}

enable()
(
    cd "$DIR"

    if ! docker ps | grep my127ws-proxy > /dev/null; then
        run docker-compose -p my127ws-proxy rm --force traefik
        run docker-compose -p my127ws-proxy up --build -d traefik
    fi
)

disable()
(
    cd "$DIR"

    if docker ps | grep my127ws-proxy > /dev/null; then
        run docker-compose -p my127ws-proxy rm --stop --force traefik
    fi
)

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=../../lib/sidekick.sh
    source "$DIR/../../lib/sidekick.sh"
}

bootstrap
main "$@"
