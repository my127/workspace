#!/bin/bash
set -e -o pipefail

composer install
composer test
