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
    local COMMAND="$*"

    if [ "$VERBOSE" = "no" ]; then

        prompt
        echo "  > ${COMMAND[*]}"
        setCommandIndicator $INDICATOR_RUNNING

        if ! bash -c "${COMMAND[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt; then

            setCommandIndicator $INDICATOR_ERROR

            cat /tmp/my127ws-stderr.txt

            echo "----------------------------------"
            echo "Full Logs :-"
            echo "  stdout: /tmp/my127ws-stdout.txt"
            echo "  stderr: /tmp/my127ws-stderr.txt"

            exit 1
            
        else
            setCommandIndicator $INDICATOR_SUCCESS
        fi
    else
        passthru "${COMMAND[@]}"
    fi
}

passthru()
{
    local COMMAND="$*"

    prompt

    echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m > $*"
    bash -e -c "${COMMAND[@]}"
}

setCommandIndicator()
{
    echo -ne "\\033[1A";
    echo -ne "\\033[$1"
    echo -n "■"
    echo -ne "\\033[0m"
    echo -ne "\\033[1E";
}
