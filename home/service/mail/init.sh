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
    if [ ! -f .flag-built ]; then
        run docker-compose -p my127ws-mail up -d --build
        touch .flag-built
    else
        run docker-compose -p my127ws-mail start
    fi
}

disable()
{
    run docker-compose -p my127ws-mail stop
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
