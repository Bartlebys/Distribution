<?php

// we load the shared variables
include  FLEXIONS_SOURCE_DIR.'/Shared.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/SwaggerToFlexionsRepresentations.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/SwaggerDelegate.php';

// we instanciate the Hypotypose singleton
$h = Hypotypose::instance();
$h->stage=DefaultStages::STAGE_DEVELOPMENT;
$h->version='1.0';
$h->classPrefix=$prefix;
if(isset($doNotGenerate)){
    $h->excludePath=$doNotGenerate;
}

$transformer=new SwaggerToFlexionsRepresentations();
$delegate=new SWaggerDelegate();
$r = $transformer->projectRepresentationFromSwaggerJson($descriptorFilePath, $prefix, $delegate);


/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}

/// Associate the global descriptor to the loop name
// Yoy must wrap it in an array
if(! $h->setLoopDescriptor($r->actions,DefaultLoops::ACTIONS)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ACTIONS);
}

/// Associate the global descriptor to the loop name
// Yoy must wrap it in an array
if(! $h->setLoopDescriptor(array($r),DefaultLoops::PROJECT)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::PROJECT);
}