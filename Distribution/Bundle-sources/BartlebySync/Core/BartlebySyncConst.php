<?php


if (!defined('BARTLEBY_SYNC_VERSION')) {

    define('BARTLEBY_SYNC_VERSION', '1.0.beta1');


// Responses key consts

    define('INFORMATIONS_KEY', 'informations');
    define('METHOD_KEY', 'method');

    define('HASHMAP_FILENAME', 'hashmap');
    define('TREE_INFOS_FILENAME', 'treeInfos');
    define('SYSTEM_DATA_PREFIX', '.');
    define('METADATA_FOLDER', '.bsync');


    define('DEBUG_MODE_WITH_REAL_TREE_ID', true);

///////////////////////////////
// BartlebySyncCommands
///////////////////////////////

    define('BCreate', 0);        // W source - un prefix the asset
    define('BUpdate', 1);        // W source - un prefix the asset
    define('BMove', 2);          // R source W destination
    define('BCopy', 3);          // R source W destination
    define('BDelete', 4);        // W source

// BartlebySyncCMDParamsRank

    define('BCommand', 0);
    define('BDestination', 1);
    define('BSource', 2);

}