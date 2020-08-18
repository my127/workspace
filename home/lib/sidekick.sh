#!/bin/bash

VERBOSE="no"

RUN_CWD=""

INDICATOR_RUNNING="34m"
INDICATOR_SUCCESS="32m"
INDICATOR_ERROR="31m"
INDICATOR_PASSTHRU="37m"

prompt()
{
    if [ "${RUN_CWD}" != "$(pwd)" ]; then
        RUN_CWD="$(pwd)"
        echo -e "\\033[1m[\\033[0m$(pwd)\\033[1m]:\\033[0m" >&2
    fi
}

run()
{
    local -r COMMAND_DEPRECATED="$*"
    local -r COMMAND=("$@")
    local DEPRECATED_MODE=no

    if [[ "${COMMAND[0]}" = *" "* ]]; then
        DEPRECATED_MODE=yes
    fi

    if [ "$VERBOSE" = "no" ]; then

        prompt
        if [ "${DEPRECATED_MODE}" = "yes" ]; then
            echo "  > ${COMMAND_DEPRECATED[*]}" >&2
            setCommandIndicator "${INDICATOR_RUNNING}"
            bash -c "${COMMAND_DEPRECATED[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt
        else
            echo "  >$(printf ' %q' "${COMMAND[@]}")" >&2
            setCommandIndicator "${INDICATOR_RUNNING}"
            "${COMMAND[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt
        fi

        # shellcheck disable=SC2181
        if [ "$?" -gt 0 ]; then
            setCommandIndicator "${INDICATOR_ERROR}"

            cat /tmp/my127ws-stderr.txt

            echo "----------------------------------" >&2
            echo "Full Logs :-" >&2
            echo "  stdout: /tmp/my127ws-stdout.txt" >&2
            echo "  stderr: /tmp/my127ws-stderr.txt" >&2

            exit 1
        else
            setCommandIndicator "${INDICATOR_SUCCESS}"
        fi
    elif [ "${DEPRECATED_MODE}" = "yes" ]; then
        passthru "${COMMAND_DEPRECATED[@]}"
    else
        passthru "${COMMAND[@]}"
    fi
}

passthru()
{
    local -r COMMAND_DEPRECATED="$*"
    local -r COMMAND=("$@")
    local DEPRECATED_MODE=no

    if [[ "${COMMAND[0]}" = *" "* ]]; then
        DEPRECATED_MODE=yes
    fi

    prompt

    if [ "${DEPRECATED_MODE}" = "yes" ]; then
        echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m > $*" >&2
        if ! bash -e -c "${COMMAND_DEPRECATED[@]}"; then
            exit 1
        fi
    else
        echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m >$(printf ' %q' "${COMMAND[@]}")" >&2
        if ! "${COMMAND[@]}"; then
            exit 1
        fi
    fi
}

setCommandIndicator()
{
    echo -ne "\\033[1A" >&2 
    echo -ne "\\033[$1" >&2
    echo -n "■" >&2
    echo -ne "\\033[0m" >&2
    echo -ne "\\033[1E" >&2
}
