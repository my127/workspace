#!/bin/bash
set -x
setup_app_volume_permissions()
{
    case "$STRATEGY" in
        "host-linux-normal")
            usermod  -u "$(stat -c '%u' /app)" build
            groupmod -g "$(stat -c '%g' /app)" build || true
            ;;
        "host-osx-normal")
            usermod  -u 1000 build
            groupmod -g 1000 build
            ;;
        *)
            exit 1
    esac

    chown build:build /app
}

resolve_volume_mount_strategy()
{
    if [ "${HOST_OS_FAMILY}" = "linux" ]; then
        STRATEGY="host-linux-normal"
    elif [ "${HOST_OS_FAMILY}" = "darwin" ]; then
        if (mount | grep "/app type fuse.osxfs") > /dev/null 2>&1; then
            STRATEGY="host-osx-normal"
        elif (mount | grep "/app type fuse.grpcfuse") > /dev/null 2>&1; then
            STRATEGY="host-osx-normal"
        else
            exit 1
        fi
    else
        exit 1
    fi
}

bootstrap()
{
    resolve_volume_mount_strategy
    setup_app_volume_permissions
}

bootstrap
exec "$@"
