<?php
/**
* A destructive installer script for PROJECT NAME*/

function logMessage($message=""){
    echo ($message."\n");
}

$today = date("Ymd-H:m:s");
logMessage ("Running installer on ".$today);

try {
    logMessage("Connecting to ".DB_NAME);
    $m = new MongoClient();

} catch (Exception $e) {
    logMessage("Mongo client must be installed ". $e->getMessage());
}
logMessage("Connected  to ".DB_NAME);


$db = $m->selectDB(DB_NAME);// Selecting  base

logMessage("Erasing all the collections if necessary");
// Erase all the collections

$collectionList=$db->listCollections();
logMessage("Number of collection ".count($collectionList));
foreach ($collectionList as $collection) {
    logMessage("Droping ".$collection->getName());
    $collection->drop();
}

logMessage("Recreating the collections");
// Collection creation
logMessage("Creating the orders collection");
$orders=$db->createCollection("orders");
logMessage("Creating the categories collection");
$categories=$db->createCollection("categories");
logMessage("Creating the users collection");
$users=$db->createCollection("users");
logMessage("Creating the tags collection");
$tags=$db->createCollection("tags");
logMessage("Creating the pets collection");
$pets=$db->createCollection("pets");
logMessage("Creating the apiresponses collection");
$apiresponses=$db->createCollection("apiresponses");
