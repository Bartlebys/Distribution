<?php

require_once FLEXIONS_MODULES_DIR.'/Deploy/FTPDeploy.php';
require_once FLEXIONS_MODULES_DIR.'/Deploy/LocalDeploy.php';


/* @var $h Hypotypose */

// /////////////////////////////////////////
// #1 Save the hypotypose to files
// /////////////////////////////////////////

hypotyposeToFiles();

// DEVELOPMENT
if ($h->stage==DefaultStages::STAGE_DEVELOPMENT){
    $deploy=new LocalDeploy($h);
    $deploy->copyFiles('/php/',dirname(__DIR__).'/www/',true);

    // AGGREGATE THE PATHS IN youdub.json
    $decodedPaths=null;
    $fl=$h->flexedList[DefaultLoops::PROJECT];
    /* @var $flexed Flexed */
    foreach ($fl as $flexed) {
        if($flexed->fileName=='pathsFragment.json'){
            $json=$flexed->source;
            $decodedPaths=json_decode($json,true);
        }
    }
    if(isset($decodedPaths)){
        // We gonna update the json
        $dataSourcePath= dirname(__DIR__) . '/App/datasources/bartleby.json';
        $dataSourceJSON=json_decode(file_get_contents($dataSourcePath),true);
        $paths=$dataSourceJSON['paths'];

        foreach ($decodedPaths['paths'] as $path => $contentAtPath ) {
            $paths[$path]=$contentAtPath;
        }
        $dataSourceJSON['paths']=$paths;
        $encoded=json_encode($dataSourceJSON,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        file_put_contents($dataSourcePath,$encoded);

    }


}