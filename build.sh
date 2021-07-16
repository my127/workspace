#!/bin/bash
set -e -o pipefail

composer install
composer compile
composer test
