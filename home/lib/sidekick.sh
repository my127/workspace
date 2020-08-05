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
        echo -e "\\033[1m[\\033[0m$(pwd)\\033[1m]:\\033[0m"
    fi
}

run()
{
    local -r COMMAND_DEPRECATED="$*"
    local -r COMMAND=("$@")
    local DEPRECATED_MODE=no

    if [[ "${COMMAND[0]}" = *" "* ]]; then
        echo "deprecated: support for passing multiple arguments in the following line will be removed in a future version" >&2
        echo "run '${COMMAND_DEPRECATED[*]}'" >&2
        echo "a future major version will only support:" >&2
        echo "run ${COMMAND_DEPRECATED[*]}" >&2
        echo >&2
        DEPRECATED_MODE=yes
    fi

    if [ "$VERBOSE" = "no" ]; then

        prompt
        if [ "${DEPRECATED_MODE}" = "yes" ]; then
            echo "  > ${COMMAND_DEPRECATED[*]}"
            setCommandIndicator "${INDICATOR_RUNNING}"
            bash -c "${COMMAND_DEPRECATED[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt
        else
            echo "  >$(printf ' %q' "${COMMAND[@]}")"
            setCommandIndicator "${INDICATOR_RUNNING}"
            "${COMMAND[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt
        fi

        if [ "$?" -gt 0 ]; then
            setCommandIndicator "${INDICATOR_ERROR}"

            cat /tmp/my127ws-stderr.txt

            echo "----------------------------------"
            echo "Full Logs :-"
            echo "  stdout: /tmp/my127ws-stdout.txt"
            echo "  stderr: /tmp/my127ws-stderr.txt"

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
        echo "deprecated: support for passing multiple arguments in the following line will be removed in a future version" >&2
        echo "passthru '${COMMAND_DEPRECATED[*]}'" >&2
        echo "a future major version will only support:" >&2
        echo "passthru ${COMMAND_DEPRECATED[*]}" >&2
        echo >&2
        DEPRECATED_MODE=yes
    fi

    prompt

    if [ "${DEPRECATED_MODE}" = "yes" ]; then
        echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m > $*"
        bash -e -c "${COMMAND_DEPRECATED[@]}"
    else
        echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m >$(printf ' %q' "${COMMAND[@]}")"
        "${COMMAND[@]}"
    fi
}

setCommandIndicator()
{
    echo -ne "\\033[1A";
    echo -ne "\\033[$1"
    echo -n "■"
    echo -ne "\\033[0m"
    echo -ne "\\033[1E";
}
