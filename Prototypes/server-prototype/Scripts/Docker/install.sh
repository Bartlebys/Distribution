#!/bin/sh

INITIAL_DIR=$(PWD)
cd "$(dirname "$0")"
./run.sh -o install.conf
cd "$INITIAL_DIR"