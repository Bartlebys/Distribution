#!/usr/bin/env bash
#
# Author : benoit@pereira-da-silva.com
# Date : 24/01/2016
# Updated on : 20/10/2016
#
# This script extracts a bundle from the current version.
# And prepare a bundle to be used by bartleby cli.

# BARTLEBY'S DISTRIBUTION MECHANISM IS STILL IN PROGRESS
# THIS HARDCODED WORKING FOLDER PATH WILL BE REPLACED BY A FLOCKER MAP


WORKING_FOLDER="/Users/bpds/Documents/Entrepot/Git/Clients/LyLo.TV/YouDubAPI"
DISTRIBUTION_BASE_FOLDER="/Users/bpds/Documents/Entrepot/Git/Bartlebys/Distribution"

BARTLEBYS_BARTLEBY_FOLDER_NAME="Bartleby"
BARTLEBYS_FLEXIONS_FOLDER_NAME="Flexions"
BARTLEBYS_SCRIPTS_FOLDER_NAME="Scripts"
BARTLEBYS_DOCKER_FILE_NAME="Dockerfile"
BARTLEBYS_INSTALLATION_DOCUMENT_FILE_NAME="Installation.md"
BARTLEBYS_DISTRIBUTION_SERVER_PROTOTYPE_RELATIVE_PATH="Prototypes/server-prototype"

BARTLEBYS_CORE="$WORKING_FOLDER/$BARTLEBYS_BARTLEBY_FOLDER_NAME"
BARTLEBYS_FLEXIONS="$WORKING_FOLDER/$BARTLEBYS_FLEXIONS_FOLDER_NAME"
BARTLEBYS_SCRIPTS="$WORKING_FOLDER/$BARTLEBYS_SCRIPTS_FOLDER_NAME"
BARTLEBYS_DOCKER_FILE="$WORKING_FOLDER/$BARTLEBYS_DOCKER_FILE_NAME"
BARTLEBYS_INSTALLATION_FILE="$WORKING_FOLDER/$BARTLEBYS_INSTALLATION_DOCUMENT_FILE_NAME"
BARTLEBYS_APP_PROTOTYPE="$DISTRIBUTION_BASE_FOLDER/$BARTLEBYS_DISTRIBUTION_SERVER_PROTOTYPE_RELATIVE_PATH"

echo "# Refreshing Distribution sources #";
echo "";
echo "We will preserve $BARTLEBYS_APP_PROTOTYPE/App.flexions";
echo "We will preserve $BARTLEBYS_APP_PROTOTYPE/html";
echo "";
echo "## Preflight ## ";
echo "";

if [ -d "$BARTLEBYS_CORE" ]; then

    echo "$BARTLEBYS_CORE found";

    if [ -d "$BARTLEBYS_FLEXIONS" ]; then

        echo "$BARTLEBYS_FLEXIONS found";
        echo "";
        echo "## Flock creation  ## ";
        echo "";

        echo "Copying  $BARTLEBYS_CORE"
        rm -Rf "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_BARTLEBY_FOLDER_NAME"
        cp -rf "$BARTLEBYS_CORE" "$BARTLEBYS_APP_PROTOTYPE/"

        echo "Copying  $BARTLEBYS_FLEXIONS"
        rm -Rf "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_FLEXIONS_FOLDER_NAME"
        cp -rf "$BARTLEBYS_FLEXIONS" "$BARTLEBYS_APP_PROTOTYPE/"

        echo "Copying  $BARTLEBYS_SYNC_MODULE"
        rm -Rf "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_BARTLEBY_SYNC_FOLDER_NAME"
        cp -rf "$BARTLEBYS_SYNC_MODULE" "$BARTLEBYS_APP_PROTOTYPE/"

        echo "Copying  $BARTLEBYS_SCRIPTS"
        rm -Rf "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_SCRIPTS_FOLDER_NAME"
        cp -rf "$BARTLEBYS_SCRIPTS" "$BARTLEBYS_APP_PROTOTYPE/"
        rm -Rf "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_SCRIPTS_FOLDER_NAME/Distribution"

        echo "Copying  $BARTLEBYS_DOCKER_FILE"
        cp -rf "$BARTLEBYS_DOCKER_FILE" "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_DOCKER_FILE_NAME"

        echo "Copying  $BARTLEBYS_INSTALLATION_FILE"
        cp -rf "$BARTLEBYS_INSTALLATION_FILE" "$BARTLEBYS_APP_PROTOTYPE/$BARTLEBYS_INSTALLATION_DOCUMENT_FILE_NAME"

        echo "Deleting _generated folders"
        find "$BARTLEBYS_APP_PROTOTYPE" -name _generated -type d -print0|xargs -0 rm -r --

        echo "Deleting files with a generated_ prefix"
        find "$BARTLEBYS_APP_PROTOTYPE" -name "generated_*" -type f -print0|xargs -0 rm -r --

        echo "Deleting out.flexions folders"
        find "$BARTLEBYS_APP_PROTOTYPE" -name out.flexions -type d -print0|xargs -0 rm -r --

        echo "Deleting out folders"
        find "$BARTLEBYS_APP_PROTOTYPE" -name out -type d -print0|xargs -0 rm -r --

        echo "Deleting Todo.md files"
        find "$BARTLEBYS_APP_PROTOTYPE" -name Todo.md -type f -print0|xargs -0 rm -r --

        else
            echo "Unable to find $BARTLEBYS_FLEXIONS"
        fi



    # Call php pack
    #php -f pack.php

else
 echo "Unable to find $BARTLEBYS_CORE";
fi