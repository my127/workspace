#!/bin/bash

VERBOSE="no"

RUN_CWD=""

INDICATOR_RUNNING="34m"
INDICATOR_SUCCESS="32m"
INDICATOR_ERROR="31m"
INDICATOR_PASSTHRU="37m"

prompt()
{
    local CWD

    CWD="$(pwd)"

    if [ "${RUN_CWD}" != "$CWD" ]; then
        RUN_CWD="$CWD"
        echo -e "\\033[1m[\\033[0m$CWD\\033[1m]:\\033[0m" >&2
    fi
}

run()
{
    local -r COMMAND_DEPRECATED="$*"
    local COMMAND=("$@")
    local DEPRECATED_MODE=no

    if [[ "${COMMAND[0]}" = *" "* ]]; then
        DEPRECATED_MODE=yes
    fi

    if [ "$VERBOSE" = "no" ]; then

        prompt
        if [ "${DEPRECATED_MODE}" = "yes" ]; then
            echo "  > ${COMMAND_DEPRECATED[*]}" >&2
            COMMAND=(bash -e -c "${COMMAND_DEPRECATED[@]}")
        else
            echo "  >$(printf ' %q' "${COMMAND[@]}")" >&2
        fi
        setCommandIndicator "${INDICATOR_RUNNING}"

        if "${COMMAND[@]}" > /tmp/my127ws-stdout.txt 2> /tmp/my127ws-stderr.txt; then
            setCommandIndicator "${INDICATOR_SUCCESS}"
        else
            setCommandIndicator "${INDICATOR_ERROR}"

            cat /tmp/my127ws-stderr.txt

            echo "----------------------------------" >&2
            echo "Full Logs :-" >&2
            echo "  stdout: /tmp/my127ws-stdout.txt" >&2
            echo "  stderr: /tmp/my127ws-stderr.txt" >&2

            return 1
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
        bash -e -c "${COMMAND_DEPRECATED[@]}"
    else
        echo -e "\\033[${INDICATOR_PASSTHRU}■\\033[0m >$(printf ' %q' "${COMMAND[@]}")" >&2
        "${COMMAND[@]}"
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

updateEnvGeneratedKey()
{
    local -r FILE="$1"
    local -r KEY="$2"
    local -r VALUE="$3"
    local TEMP_FILE
    local LINE

    TEMP_FILE="$(mktemp "$FILE.XXXXXX")"

    if [ -f "$FILE" ]; then
        while IFS= read -r LINE || [ -n "$LINE" ]; do
            case "$LINE" in
                "$KEY="*) ;;
                *) printf '%s\n' "$LINE" >> "$TEMP_FILE" ;;
            esac
        done < "$FILE"
    fi

    printf '%s=%s\n' "$KEY" "$VALUE" >> "$TEMP_FILE"
    mv "$TEMP_FILE" "$FILE"
}
