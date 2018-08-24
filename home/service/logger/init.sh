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
    if [ ! -f .flag-built ]; then 
        run docker-compose -p my127ws-logger up -d --build
        touch .flag-built
    else
        run docker-compose -p my127ws-logger start
    fi
}

disable()
{
    run docker-compose -p my127ws-logger stop
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    . "$DIR/../../lib/sidekick.sh"

    cd $DIR
}

bootstrap
main $@
