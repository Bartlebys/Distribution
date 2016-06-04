#!/usr/bin/env bash



cd ./Bartleby/Commons.flexions/EndPointsFromDefinitions/
php -f run.php

cd ../../../

cd ./Bartleby/Commons.flexions/App/
php -f run.php

cd ../../../

cd ./YouDubApi.flexions/EndPointsFromDefinitions/
php -f run.php

cd ../../

cd ./App.flexions/App/
php -f run.php

cd ../../