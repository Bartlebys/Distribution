<?php


require_once BARTLEBY_ROOT_FOLDER.'/Core/Stages.php';
use Bartleby\Core\Stages;

if (!defined('BARTLEBY_SYNC_ROOT_PATH')) {

    define('BARTLEBY_SYNC_ROOT_PATH', dirname(__FILE__).'/');

    ///////////////////////
    // PERSITENCY
    ///////////////////////

    $a=BARTLEBY_SYNC_ROOT_PATH . 'Core/IOManagerFS.php';
    require_once BARTLEBY_SYNC_ROOT_PATH . 'Core/IOManagerFS.php';  // Default adapter
    define('PERSISTENCY_CLASSNAME', 'BartlebySync\Core\IOManagerFS');

    //////////////////////
    // MISC
    //////////////////////

    define('MIN_TREE_ID_LENGTH', 1);
    define('CLEAN_UP_ON_ERROR',false);

}
