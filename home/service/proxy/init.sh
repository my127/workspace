#!/bin/bash

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
    if [ ! "$(docker ps | grep my127ws-proxy)" ]; then
        if [ "$(docker ps -aq -f name=my127ws-proxy)" ]; then
            { cd "${DIR}"; run docker-compose -p my127ws-proxy rm --force traefik; }
        fi
        { cd "${DIR}"; run docker-compose -p my127ws-proxy up --build -d traefik; }
    fi
}

disable()
{
    if [ "$(docker ps | grep my127ws-$1)" ]; then
        { cd "${DIR}"; run docker-compose -p my127ws-proxy rm --stop --force traefik; }
    fi
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    . "$DIR/../../lib/sidekick.sh"
}

bootstrap
main $@
