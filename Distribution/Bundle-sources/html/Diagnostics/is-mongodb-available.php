<?php

namespace Bartleby;

require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

$directory=dirname(__DIR__).'/';
$configuration=new Configuration($directory,BARTLEBY_ROOT_FOLDER);;

use MongoClient;

if (class_exists('MongoClient')) {
    print "PHP Legacy Mongo client is Installed";
}
else {
    print "PHP Legacy Mongo  client is not Installed";
}



/*
use \MongoDB\Driver ;


if (class_exists('Driver')) {
    $client=new Driver;
    print "PHP MongoDB client is Installed";
}
else {
    print "PHP MongoDB client is not Installed";
}

print(' (You should may be start the daemon : sudo mongod)');

*/