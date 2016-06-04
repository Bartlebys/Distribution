<?php

if (!defined('BARTLEBY_FLEXIONS_ENVIRONMENT')){

    define('BARTLEBY_FLEXIONS_ENVIRONMENT',true);

    // Define the absolutes xOS clients generative export path
    define('xOS_APP_EXPORT_PATH','AP_xOS_AEP');
    define('xOS_BARTLEBYS_COMMONS_EXPORT_PATH','AP_xOS_BCEP');

    // Define relative path from Bartleby's parent folder to the public App Root Folder
    // You adjust this relative path
    // The most current configuration is :
    //  /Bartleby/              <- Bartleby's framework
    //  /Barleby_xxx/           <- Bartleby's modules
    //  /www/                   <- Bartleby's app document root
    define('APP_PUBLIC_ROOT_FOLDER','AP_PRF');

}