<?php
/**
 *
 * This script should be destroyed and not deployed.
 * A destructive installer script for YouDub
 *
 **/
namespace Bartleby;

require_once dirname(__DIR__) . '/Configuration.php';

use \MongoClient;
use Bartleby\Core\Stages;

error_reporting(E_ALL);
ini_set('display_errors', 1);


$configuration = new Configuration(dirname(__DIR__), BARTLEBY_ROOT_FOLDER);

function logMessage($message = "") {
    echo($message . "<br>\n");
}

$today = date("Ymd-H:m:s");
logMessage("Starting Ephemeral remover on " . $today);
try {
    logMessage("Connecting to MONGO");
    $m = new MongoClient();
} catch (Exception $e) {
    logMessage("Mongo client must be installed " . $e->getMessage());
}
logMessage("Selecting the database  " . $configuration->MONGO_DB_NAME());
$db = $m->selectDB($configuration->MONGO_DB_NAME());// Selecting  base

$collectionList = $db->listCollections();

logMessage("Erasing all the collections if necessary");
// Erase all the collections

logMessage("Number of collection " . count($collectionList));
foreach ($collectionList as $collection) {
    logMessage("Cleaning " . $collection->getName());
    $options = array(
        "w" => 1,
        "j" => true
    );
    $q = array(EPHEMERAL_KEY => '1');
    try {
        $r = $collection->remove($q, $options);
        if ($r['ok'] == 1) {
            logMessage($r['n'] . " deletion.");
        }
    } catch (\Exception $e) {
        logMessage("An exception has occurred: " . $e->getMessage());
    }

}