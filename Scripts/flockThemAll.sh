#!/usr/bin/env bash

PREVIOUS_DIR=$(PWD)
SCRIPT_DIRECTORY="$(dirname "$0")"
BASE_DIRECTORY="$(dirname "$SCRIPT_DIRECTORY")"
FLOCKER_FOLDER="$BASE_DIRECTORY/Flocker"
FLOCKED_DISTRIBUTION_FOLDER="$BASE_DIRECTORY/Distribution/Flocked"
PROTOTYPES_FOLDER="$BASE_DIRECTORY/Prototypes"

echo "BASE_DIRECTORY: $BASE_DIRECTORY";

cd $FLOCKER_FOLDER



# macOS app
SOURCE="$PROTOTYPES_FOLDER/macOS-app-prototype"
DESTINATION="$FLOCKED_DISTRIBUTION_FOLDER/macOS-app-prototype"
php -f pack.php args --source $SOURCE  --destination $DESTINATION


# server
SOURCE="$PROTOTYPES_FOLDER/server-prototype"
DESTINATION="$FLOCKED_DISTRIBUTION_FOLDER/server-prototype"
php -f pack.php args --source $SOURCE  --destination $DESTINATION

cd "$PREVIOUS_DIR"