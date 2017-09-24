#!/bin/sh
#
# Usage
#
# Run with default values:
#  ./run.sh it will use the default.conf
#
# Run with a specific configuration :
#   ./run.sh -o configuration.conf
#
# To proceed to install
#   ./run.sh -o ./install.conf

clear
PREVIOUS_DIR=$(pwd)
cd "$(dirname "$0")"

echo "Loading default configuration"
source default.conf

# Arguments parsing
while [[ $# -gt 0 ]]
do
  key="$1"
  case $key in
      -o|--options-file)
      OPTIONS_FILE="$2"
      ;;
      *)
      #
      ;;
  esac
  shift
done

# Import the options file?
if [ -z ${OPTIONS_FILE+x} ];
    then
    echo "Command line arguments are overriden by a configuration file."
    else
    echo "Using options file $OPTIONS_FILE"
    source $OPTIONS_FILE;
fi;


cd ../../
BASE_DIR=$(pwd)
echo "Changing directory to $BASE_DIR"


# Image name must be in lower case
if [ -z "IMAGE_NAME" ];
    then
        echo "IMAGE_NAME is undefined "
    else
       IMAGE_NAME=$(echo $IMAGE_NAME | awk '{print tolower($0);}' )
fi;


echo "";
echo OPTIONS_FILE: $OPTIONS_FILE
echo PULL_IMAGE: $PULL_IMAGE
echo INSTALL: $INSTALL
echo CONTAINER_NAME: $CONTAINER_NAME
echo IMAGE_NAME: $IMAGE_NAME
echo DESTROY_IMAGE: $DESTROY_IMAGE
echo XDEBUG: $XDEBUG
echo APACHE_PORT: $APACHE_PORT
echo MONGO_DB_PORT: $MONGO_DB_PORT
echo POST_PROCESSING_SCRIPT: $POST_PROCESSING_SCRIPT

# Stop the container
echo "Stopping the container $CONTAINER_NAME"
docker stop $CONTAINER_NAME

# Delete the container
echo "Removing the container"
docker rm $CONTAINER_NAME

if [[ "$PULL_IMAGE" =~ ^YES ]];
  then
    echo "Pulling bartlebys/php-apache-mongo"
    # Let's pull the base Image
    docker pull bartlebys/php-apache-mongo
fi;

if [[ ("$INSTALL" =~ ^YES) || ("$DESTROY_IMAGE" =~ ^YES) ]];
  then
    # Delete the image if necessary
    docker rmi $IMAGE_NAME:latest
    # Build the youdubserver image
    echo "Building the image $IMAGE_NAME with the current sources"
    docker build -t $IMAGE_NAME:latest .

    #Create if necesary the mongo folders
    mkdir $BASE_DIR/Mongo/db
    mkdir $BASE_DIR/Mongo/configdb
fi;

# Run YouDubApi container
echo "Running the container $CONTAINER_NAME"

if [[ "$XDEBUG" =~ ^YES ]];
    then

        #################
        # ENABLE XDEBUG
        #################

         # Grab the Host IP
        HOST_IP=$(ifconfig en0 | grep inet | grep -v inet6 | awk '{print $2}')

        # Run the debuggable container
        # details available on https://github.com/Bartlebys/Php-Apache-Mongo/blob/master/README.md

        docker run  -e PHP_IDE_CONFIG="serverName=$SERVERNAME"\
                    -e XDEBUG_CONFIG="idekey=PHPSTORM"\
                    -e XDEBUG_CONFIG="remote_host=$HOST_IP"\
                    -p $MONGO_DB_PORT:27017 \
                    -p $APACHE_PORT:80\
                    -v $BASE_DIR:/var/www\
                    -v $BASE_DIR/Mongo/db:/data/db\
                    -v $BASE_DIR/Mongo/configdb:/data/configdb\
                    -d --name $CONTAINER_NAME $IMAGE_NAME

else

    ###################
    # No XDEBUG Support
    ###################

    docker run  -p $APACHE_PORT:80\
                -p $MONGO_DB_PORT:27017\
                -v $BASE_DIR:/var/www\
                -v $BASE_DIR/Mongo/db:/data/db\
                -v $BASE_DIR/Mongo/configdb:/data/configdb\
                -d --name $CONTAINER_NAME $IMAGE_NAME
fi;

# Start mongod
echo "Starting the mongodb daemon in the container "
# Since a january 2017 mongod fails to be launched via:
# `docker exec $CONTAINER_NAME service mongod start`
# So we launch the service via exec in a background task.
docker exec -d $CONTAINER_NAME mongod

# Run the post processing script
if [ -z ${POST_PROCESSING_SCRIPT+x} ];
    then
        echo "There is no post processing script to run"
    else
        echo "Running post processing script $POST_PROCESSING_SCRIPT"
        $POST_PROCESSING_SCRIPT
fi;

# Open localhost in a browser on macOS
if [[ "$OSTYPE" =~ ^darwin ]];
    then open http://yd.local:$APACHE_PORT/
fi;

cd "$PREVIOUS_DIR"