# Docker Quick Notes

- By [bpds] (https://pereira-da-silva.com/)
- Dated : 2016/09/29

# How to install on macOS ?

1. [Download and install docker for mac](https://download.docker.com/mac/stable/Docker.dmg)
2. Optional: Download and install Kitematic 

## Run the install script 

Go to the `YouDubApi/`. This script is destructive it will stop and remove the container, build the image and start a container.

```shell
./install.sh
```

It may be a long process in case of success if you are running on macOS your browser will display Bartleby's start page). We build two images a base image *phpmongoapache* and the *youdubserver* image.

## Add `yd.local` to you etc/Hosts

`sudo nano /etc/hosts`

```
# Development Hosts
127.0.0.1       yd.repository.local
127.0.0.1       yd.local
```



# Usage Of the container

## Run the container and start the services

# run.sh Usage

If you call `./run.sh` it will use the options set in [`default.conf`](default.conf)).
The more convenient way to run is should create a `configuration.conf` file and call `./run.sh -o configuration.conf`


[run.sh Script] (XDebug/PHPStorm/run.sh)

### To proceed to install

```
 ./install.sh
```

For more detail on an equivalent run command go to [Bartleby's Php-Apache-Mongo](https://github.com/Bartlebys/Php-Apache-Mongo/blob/master/README.md)
                                    

## Access to Bartleby UI

Open [localhost](http://localhost/) in your browser.

## Stop the container

```shell
docker stop YouDubApi
```
## Delete the container

```shell
docker rm YouDubApi
```
# Useful commands

## Stop all containers

```shell
docker stop $(docker ps -a -q)
```

## Delete all containers

```shell
docker rm $(docker ps -a -q)
```


## Interactive bash

```shell
docker run -t -i youdubserver /bin/bash
```

## How to pull the base Image?

```shell
docker pull bartlebys/php-apache-mongo
```



## Other commands

```shell
docker logs --details YouDubApi

# Run sh in YouDubApi
bash -c "clear && docker exec -it YouDubApi sh"
```

# You need more informations?

 + [Bartleby's Php-Apache-Mongo](https://github.com/Bartlebys/Php-Apache-Mongo/blob/master/README.md)
 + [Docker's site](https://www.docker.com/products/docker#/mac)
 + [Docker Hub](https://hub.docker.com)
 + [User guide](https://docs.docker.com/engine/userguide/)
 + [Docker Repo] (https://github.com/docker-library/repo-info/tree/master/repos)