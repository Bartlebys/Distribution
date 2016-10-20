<?php

namespace Bartleby;

require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

$directory=dirname(__DIR__).'/';
$configuration=new Configuration($directory,BARTLEBY_ROOT_FOLDER);;

use MongoClient;

if (COMMANDLINE_MODE){
    echo ('CLI mode'.CR);
}

if (class_exists('MongoClient')) {
    echo "PHP Legacy Mongo client is Installed ".CR;
}
else {
    echo "PHP Legacy Mongo  client is not Installed ".CR;
}

use \MongoDB\Driver ;

if (class_exists('Driver')) {
    $client=new Driver;
    echo "PHP MongoDB client is Installed".CR;
}
else {
    echo "PHP MongoDB client is not Installed".CR;
}

$today = date("Ymd-H:m:s");
echo ("Connection attempt on ".$today.CR);
try {
    echo("Connecting to MONGO".CR);
    $m = new \MongoClient();
} catch (Exception $e) {
    echo("Mongo client must be installed ". $e->getMessage().CR);
}
echo("Selecting the database  ".$configuration->MONGO_DB_NAME().CR);
$db = $m->selectDB($configuration->MONGO_DB_NAME());// Selecting  base

$collectionList=$db->listCollections();
foreach ($collectionList as $collection) {
    echo("-$collection".CR);
}