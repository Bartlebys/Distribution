<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */


if (isset ( $f )) {
    $f->fileName = 'generated_destructiveInstaller.php';
    $f->package = "php/Protected/";
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>

/**
*
* This script should be destroyed and not deployed.
* A destructive installer script for YouDub
*
**/
require_once dirname(__DIR__).'/Configuration.php';


use Bartleby\Core\Stages;
use Bartleby\Configuration;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$configuration=new Configuration(dirname(__DIR__),BARTLEBY_ROOT_FOLDER);

function logMessage($message=""){
    echo ($message."<br>\n");
}

$today = date("Ymd-H:m:s");
logMessage ("Running installer on ".$today);
try {
    logMessage("Connecting to MONGO");
    $m = new MongoClient();
} catch (Exception $e) {
    logMessage("Mongo client must be installed ". $e->getMessage());
}
logMessage("Selecting the database  ".$configuration->MONGO_DB_NAME());
$db = $m->selectDB($configuration->MONGO_DB_NAME());// Selecting  base

$collectionList=$db->listCollections();

if ($configuration->STAGE()==Stages::PRODUCTION){
    logMessage("Destructive installer is blocked on Production stage");
    return;
}

if ( $configuration->ALLOW_DESTRUCTIVE_INSTALLER()===false && count($collectionList)>0 ){
    logMessage("ALLOW_DESTRUCTIVE_INSTALLER is set to FALSE! ");
    logMessage("Turn it to true once if you are sure you want to totally reset the DB.");
    return;
}

logMessage("Erasing all the collections if necessary");
// Erase all the collections

logMessage("Number of collection ".count($collectionList));
foreach ($collectionList as $collection) {
logMessage("Droping ".$collection->getName());
$collection->drop();
}
logMessage("Recreating the collections");

// Collection creation

// Bartleby's commons
logMessage("Creating the 'users' collection");
$users=$db->createCollection("users");
logMessage("Creating 'ephemeral' Index for 'users'");
$users->createIndex(array("ephemeral" => 1));
logMessage("Creating the 'groups' collection");
$groups=$db->createCollection("groups");
logMessage("Creating 'ephemeral' Index for 'groups'");
$groups->createIndex(array("ephemeral" => 1));
logMessage("Creating the 'permissions' collection");
$permissions=$db->createCollection("permissions");
logMessage("Creating 'ephemeral' Index for 'permissions'");
$permissions->createIndex(array("ephemeral" => 1));
logMessage("Creating the 'triggers' collection");
$triggers=$db->createCollection("triggers");
logMessage("Creating 'ephemeral' Index for 'triggers'");
$triggers->createIndex(array("ephemeral" => 1));

<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }
    $shouldBeExcluded=false;
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos(strtolower($name), strtolower($actionTobeExcluded)) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }

    if ($shouldBeExcluded==true){
        continue;
    }

    $pluralized=lcfirst(Pluralization::pluralize($name));
    echoIndentCR('logMessage("Creating the \''.$pluralized.'\' collection");',0);
    echoIndentCR('$'.$pluralized.'=$db->createCollection("'.$pluralized.'");',0);
    echoIndentCR('logMessage("Creating \'ephemeral\' Index for \''.$pluralized.'\'");',0);
    echoIndentCR('$'.$pluralized.'->createIndex(array("ephemeral" => 1));',0);

}
?>

logMessage("");
logMessage('deleting cookies ('.count($_COOKIE).')');
    foreach ($_COOKIE as $k=>$v) {
    setcookie($k,'',time()-60,'/', null, false, false);
}


logMessage("");
logMessage("**********************************************************************");
logMessage("Please set  Configuration::ALLOW_DESTRUCTIVE_INSTALLER const to FALSE!");



require_once BARTLEBY_PUBLIC_FOLDER.'Protected/PostInstaller.php';
use Bartleby\PostInstaller;
$postInstaller=new PostInstaller();
$postInstaller->run($configuration);

<?php /*<- END OF TEMPLATE */?>
