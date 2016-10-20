<?php

// We can deploy the files per version and stage
// And keep a copy in the out.YouDubApi-flexions-App folder.

require_once FLEXIONS_MODULES_DIR . '/Deploy/LocalDeploy.php';


$h = Hypotypose::Instance();
$r = Registry::Instance();
$bartlebysCommons_xOS_exportPath=$r->valueForKey('bartlebysCommons_xOS_exportPath');
$eraseFilesOnGenerationOfBartlebysCommons=$r->valueForKey('eraseFilesOnGenerationOfBartlebysCommons');

if (isset($bartlebysCommons_xOS_exportPath)){
    if ($h->stage == DefaultStages::STAGE_DEVELOPMENT) {
        $deploy = new LocalDeploy($h);
        $generatedFolder = dirname(dirname(__DIR__)) . '/Commons/_generated';
        if (isset($eraseFilesOnGenerationOfBartlebysCommons)) {
            if ($eraseFilesOnGenerationOfBartlebysCommons) {
                // WE DELETE THE GENERATED FOLDER BEFORE TO REGENERATE
                $deploy->rmPath($generatedFolder);
            }
        }
        $deploy->flatCopyFilesInPackage('/php/_generated', $generatedFolder . '/');
        $deploy->flatCopyFilesInPackage('/php/api/v1/_generated/Endpoints', $generatedFolder . '/EndPoints');
        $deploy->flatCopyFilesInPackage('/php/api/v1/_generated/Models', $generatedFolder . '/Models');

        if (isset($eraseFilesOnGenerationOfBartlebysCommons)) {
            if ($eraseFilesOnGenerationOfBartlebysCommons) {
                $deploy->rmPath($bartlebysCommons_xOS_exportPath);
            }
        }
        $deploy->copytFilesInPackage('/xOS/', $bartlebysCommons_xOS_exportPath, true);
    }
}else{
    fLog('bartlebysCommons_xOS_exportPath and bartlebysCommons_xOS_exportPath must be defined check your build configuration constants.',true);
}
