#!/usr/bin/env bash

CURRENT_DIR=$(PWD)
cd "$(dirname "$0")"

rm -Rf ../../html/files/*
rm -Rf ../../html/files/.*

cd "$CURRENT_DIR"