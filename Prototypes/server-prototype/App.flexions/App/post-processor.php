<?php

// We can deploy the files per version and stage
// And keep a copy in the out.YouDubApi-flexions-App folder.
require_once FLEXIONS_MODULES_DIR . '/Deploy/LocalDeploy.php';

$h = Hypotypose::Instance();
$r = Registry::Instance();
$app_xOS_exportPath=$r->valueForKey('app_xOS_exportPath');
$eraseFilesOnGenerationOfYouDubApp=$r->valueForKey('eraseFilesOnGenerationOfYouDubApp');

if (isset($app_xOS_exportPath)){
    if ($h->stage==DefaultStages::STAGE_DEVELOPMENT){
        $deploy=new LocalDeploy($h);
        $www=dirname(dirname(__DIR__)).'/html';
        $generatedPath=$www.'/api/v1/_generated';
        if(isset($eraseFilesOnGenerationOfYouDubApp)){
            if($eraseFilesOnGenerationOfYouDubApp){
                $deploy->rmPath($www.'/api/v1/_generated');
            }
        }
        $deploy->copytFilesInPackage('/php/api/v1/_generated',$www,true);
        $deploy->copytFilesInPackage('/php/Protected',$www,true);
        if(isset($eraseFilesOnGenerationOfYouDubApp)){
            if($eraseFilesOnGenerationOfYouDubApp){
                // We want to copy the package 'ios/' files to the iOS sources
                $deploy->rmPath($app_xOS_exportPath);
            }
        }
        $deploy->copytFilesInPackage('/xOS/',$app_xOS_exportPath,true);
    }
}else{
    fLog('app_xOS_exportPath is not defined check your build configuration constants.',true);
}


