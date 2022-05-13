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

    if ! docker ps | grep my127ws-proxy2 > /dev/null; then

        if [ ! -d "traefik/root/tls" ]; then
            run mkdir -p traefik/root/tls
        fi

        run curl --fail --location --output traefik/root/tls/my127.site.crt "$(ws global config get global.service.proxy.https.crt)"
        run curl --fail --location --output traefik/root/tls/my127.site.key "$(ws global config get global.service.proxy.https.key)"
        run docker-compose -p my127ws-proxy2 up --force-recreate --build -d traefik
    fi
)

disable()
(
    cd "$DIR"

    if docker ps | grep my127ws-proxy2 > /dev/null; then
        run docker-compose -p my127ws-proxy2 rm --stop --force traefik
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
