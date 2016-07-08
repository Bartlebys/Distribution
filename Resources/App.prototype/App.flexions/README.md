## YouDub Api Generator

+ Generation of the server side api.
+ Generation of the xOS client side library 

## How to ? ##

Pre-generate the api descriptor 

    cd <path>/EndPointsFromDefinitions/
    php -f run.php


Generate the app

    cd <path>/App/
    php -f run.php

The generated files will be available in the folder out.flexions/
The logs will be written in BarltebyFlexions folder /out/

## Regenerate all the stack ##

You can create a global flexions script.

    #!/usr/bin/env bash
    
    cd ./Bartleby/Commons.flexions/EndPointsFromDefinitions/
    php -f run.php
    
    cd ../../../
    
    cd ./Bartleby/Commons.flexions/App/
    php -f run.php
    
    cd ../../../
    
    cd ./App.flexions/EndPointsFromDefinitions/
    php -f run.php
    
    cd ../../
    
    cd ./App.flexions/App/
    php -f run.php
    
    cd ../../
