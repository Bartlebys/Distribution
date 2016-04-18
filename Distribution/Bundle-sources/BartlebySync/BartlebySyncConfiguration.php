<?php

if (!defined('BARTLEBY_SYNC_ROOT_PATH')) {

    define('BARTLEBY_SYNC_ROOT_PATH', dirname(__FILE__).'/');

    ///////////////////////
    // KEYS
    ///////////////////////

    define('BARTLEBY_SYNC_CREATIVE_KEY', 'AP_BS_SCK'); // Used to validate a tree creation
    define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK'); // Used create the data system folder

    ///////////////////////
    // REPOSITORY
    ///////////////////////

    define('REPOSITORY_HOST','AP_BS_RH');
    define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'AP_BS_WP'.DIRECTORY_SEPARATOR);

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