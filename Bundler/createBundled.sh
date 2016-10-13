#!/usr/bin/env bash
#
# Author : benoit@pereira-da-silva.com
# Date : 24/01/2016
# Updated on : 06/09/2016
#
# This script extracts a bundle from the current version.
# And prepare a bundle to be used by bartleby cli.

# BARTLEBY IS IN PROGRESS
# THIS HARDCODED WORKING FOLDER PATH WILL BE REPLACED BY A BUNDLER MAP

PREVIOUS_DIR=$(PWD)
cd "$(dirname "$0")"
BUNDLER_FOLDER=$(PWD)

WORKING_FOLDER="/Users/bpds/Documents/Entrepot/Git/Clients/LyLo.TV/YouDubAPI"
BARTLEBYS_CORE="$WORKING_FOLDER/Bartleby"
BARTLEBYS_FLEXIONS="$WORKING_FOLDER/Flexions"
BARTLEBYS_SYNC_MODULE="$WORKING_FOLDER/BartlebySync"
BARTLEBYS_APP_PROTOTYPE="$BUNDLER_FOLDER/../Resources/App.prototype"

BUNDLE_SOURCES="../Distribution/Bundle-sources/"

echo "# Bundle generation #";
echo "";
echo "## Preflight ## ";
echo "";

if [ -d "$BARTLEBYS_CORE" ]; then

    echo "$BARTLEBYS_CORE found";

    if [ -d "$BARTLEBYS_FLEXIONS" ]; then

         echo "$BARTLEBYS_FLEXIONS found";

          if [ -d "$BARTLEBYS_SYNC_MODULE" ]; then

                echo "$BARTLEBYS_SYNC_MODULE found";
                echo "";
                echo "## Bundle creation  ## ";
                echo "";

            if [ -d "$BUNDLE/" ];then
                echo "Deleting $BUNDLE "
                rm -Rf $BUNDLE_SOURCES
            fi

            echo "Creating $BUNDLE"
            mkdir $BUNDLE_SOURCES

            echo "Copying  $BARTLEBYS_CORE"
            cp -rf $BARTLEBYS_CORE $BUNDLE_SOURCES/

            echo "Copying  $BARTLEBYS_FLEXIONS"
            cp -rf $BARTLEBYS_FLEXIONS $BUNDLE_SOURCES/

            echo "Copying  $BARTLEBYS_SYNC_MODULE"
            cp -rf $BARTLEBYS_SYNC_MODULE $BUNDLE_SOURCES/

            echo "Copying  $BARTLEBYS_APP_PROTOTYPE"
            cp -rf $BARTLEBYS_APP_PROTOTYPE/ $BUNDLE_SOURCES/

            echo "Deleting $BUNDLE/README.md"
            rm $BUNDLE_SOURCES/README.md

            echo "Deleting _generated folders"
            find $BUNDLE_SOURCES -name _generated -type d -print0|xargs -0 rm -r --

            echo "Deleting files with a generated_ prefix"
            find $BUNDLE_SOURCES -name "generated_*" -type f -print0|xargs -0 rm -r --

            echo "Deleting out.flexions folders"
            find $BUNDLE_SOURCES -name out.flexions -type d -print0|xargs -0 rm -r --

            echo "Deleting out folders"
            find $BUNDLE_SOURCES -name out -type d -print0|xargs -0 rm -r --

            echo "Deleting Todo.md files"
            find $BUNDLE_SOURCES -name Todo.md -type f -print0|xargs -0 rm -r --

            echo "Injecting BartlebySync Configuration"
            cp $BARTLEBYS_APP_PROTOTYPE/Modules/BartlebySyncConfiguration.php $BUNDLE_SOURCES/BartlebySync/
            echo ""

          else
                echo "Unable to find $BARTLEBYS_FLEXIONS"
          fi

    else
         echo "Unable to find $BARTLEBYS_SYNC_MODULE"
    fi

    # Call php pack
    php -f pack.php

else
 echo "Unable to find $BARTLEBYS_CORE";
fi

cd "$PREVIOUS_DIR"