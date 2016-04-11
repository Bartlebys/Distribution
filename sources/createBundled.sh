#!/usr/bin/env bash
#
# Author : benoit@pereira-da-silva.com
# Date : 24/01/2016
#
# This script extracts a bundle from the current version.
# And prepare a bundle to be used by bartleby cli.

# BARTLEBY IS IN PROGRESS
# THIS HARDCODED WORKING FOLDER PATH WILL BE REPLACED BY A BUNDLER MAP
WORKING_FOLDER="/Users/bpds/Documents/Entrepot/Git/Clients/LyLo.TV/YouDubAPI"
BARTLEBYS_CORE="$WORKING_FOLDER/Bartleby"
BARTLEBYS_BUNDLER="$WORKING_FOLDER/BartlebyBundler"
BARTLEBYS_FLEXIONS="$WORKING_FOLDER/BartlebyFlexions"
BARTLEBYS_SYNC_MODULE="$WORKING_FOLDER/BartlebySync"
BARTLEBYS_APP_PROTOTYPE="$WORKING_FOLDER/App.prototype"

BUNDLE="../../Distribution/Bundle-sources/"

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
                rm -Rf $BUNDLE
            fi

            echo "Creating $BUNDLE"
            mkdir $BUNDLE

            echo "Copying  $BARTLEBYS_CORE"
            cp -rf $BARTLEBYS_CORE $BUNDLE/

            echo "Copying  $BARTLEBYS_BUNDLER"
            cp -rf $BARTLEBYS_BUNDLER $BUNDLE/

            echo "Copying  $BARTLEBYS_FLEXIONS"
            cp -rf $BARTLEBYS_FLEXIONS $BUNDLE/

            echo "Copying  $BARTLEBYS_SYNC_MODULE"
            cp -rf $BARTLEBYS_SYNC_MODULE $BUNDLE/

            echo "Copying  $BARTLEBYS_APP_PROTOTYPE"
            cp -rf $BARTLEBYS_APP_PROTOTYPE/ $BUNDLE/

            echo "Deleting $BUNDLE/README.md"
            rm $BUNDLE/README.md

            echo "Deleting _generated folders"
            find $BUNDLE -name _generated -type d -print0|xargs -0 rm -r --

            echo "Deleting files with a generated_ prefix"
            find $BUNDLE -name "generated_*" -type f -print0|xargs -0 rm -r --

            echo "Deleting out.flexions folders"
            find $BUNDLE -name out.flexions -type d -print0|xargs -0 rm -r --

            echo "Deleting out folders"
            find $BUNDLE -name out -type d -print0|xargs -0 rm -r --

            echo "Deleting Todo.md files"
            find $BUNDLE -name Todo.md -type f -print0|xargs -0 rm -r --

            echo "Injecting BartlebySync Configuration"
            cp $BARTLEBYS_APP_PROTOTYPE/Modules/BartlebySyncConfiguration.php $BUNDLE/BartlebySync/
            echo ""

          else
                echo "Unable to find $BARTLEBYS_FLEXIONS"
          fi

    else
         echo "Unable to find $BARTLEBYS_SYNC_MODULE"
    fi

else
 echo "Unable to find $BARTLEBYS_CORE";
fi