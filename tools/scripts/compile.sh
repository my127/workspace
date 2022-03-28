#!/bin/bash

compile()
{
    local version="$1"
    
    echo "$version" > home/build
    bin/build && box compile
}

compile "$1"
