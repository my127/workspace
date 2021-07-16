#!/bin/bash

compile()
{
    local version="${1:-$(date -u)}"
    
    echo "$version" > home/version
    bin/build && box compile
}

compile "$1"
