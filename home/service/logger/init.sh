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
}

enable()
{
    MY127WS_PROXY_DOMAIN="$(ws global config get global.service.proxy.domain)"
    export MY127WS_PROXY_DOMAIN

    run docker-compose -p my127ws-logger up -d --build
    touch .flag-built
}

disable()
{
    run docker-compose -p my127ws-logger stop
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=../../lib/sidekick.sh
    source "$DIR/../../lib/sidekick.sh"

    cd "$DIR"
}

bootstrap
main "$@"
