#!/bin/bash

set -o errexit
set -o nounset
set -o pipefail

DIR=""

main()
{
    if [[ "$1" = "enable" ]]; then
        enable
        exit
    fi

    if [[ "$1" = "disable" ]]; then
        disable
        exit
    fi

    if [[ "$1" = "restart" ]]; then
        restart
        exit
    fi
}

enable()
{
    local TRAEFIK_MAIL_RULE

    TRAEFIK_MAIL_RULE="$(ws global service proxy config rule mail)"
    update_env_generated_key ".env" "TRAEFIK_MAIL_RULE" "${TRAEFIK_MAIL_RULE}"

    if [[ ! -f .flag-built ]]; then
        run docker-compose -p my127ws-mail up -d --build
        touch .flag-built
    else
        run docker-compose -p my127ws-mail up -d
    fi
}

disable()
{
    run docker-compose -p my127ws-mail stop
}

restart()
{
    disable
    enable
}

bootstrap()
{
    DIR="$(cd "$(dirname "$0")" && pwd)"
    # shellcheck source=home/lib/sidekick.sh
    source "${DIR}/../../lib/sidekick.sh"

    cd "${DIR}"
}

bootstrap
main "$@"
