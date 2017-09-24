#!/bin/sh

INITIAL_DIR=$(pwd)
cd "$(dirname "$0")"
./run.sh -o install.conf
cd "$INITIAL_DIR"