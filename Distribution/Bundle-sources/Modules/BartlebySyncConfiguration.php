<?php

if (!defined('BARTLEBY_SYNC_ROOT_PATH')) {

    define('BARTLEBY_SYNC_ROOT_PATH', dirname(__FILE__).'/');

    ///////////////////////
    // KEYS
    ///////////////////////

    
    define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK'); // Used create the data system folder

    ///////////////////////
    // REPOSITORY
    ///////////////////////


    // At this point $configuration is set
    $stage=$configuration->STAGE();

    if ($stage==Stages::DEVELOPMENT){
        define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK_LOCAL'); // Used create the data system folder
        define('REPOSITORY_HOST','AP_BS_RH_DEVELOPMENT');
        define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }elseif($stage==Stages::PRODUCTION){
        define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK_PRODUCTION'); // Used create the data system folder
        define('REPOSITORY_HOST','AP_BS_RH_PRODUCTION');
        define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }elseif($stage==Stages::ALTERNATIVE){
        define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK_ALTERNATIVE'); // Used create the data system folder
        define('REPOSITORY_HOST','AP_BS_RH_ALTERNATIVE');
        define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }else{
        // It is local Stages::LOCAL
        define('BARTLEBY_SYNC_SECRET_KEY', 'AP_BS_SSK_LOCAL'); // Used create the data system folder
        define('REPOSITORY_HOST','AP_BS_RH_LOCAL');
        define('REPOSITORY_WRITING_PATH', dirname(__DIR__) .DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
    }


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