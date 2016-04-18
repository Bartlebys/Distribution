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


// If you add a path to the preserve path it will be generated  only
// If the file does not already exists.
// To regenerate delete it and re proceed to YouDubApi-flexions-App
// SAMPLES :
//$h->preservePath[]='api/'.$h->majorVersionPathSegmentString().'Api.php';
//$h->preservePath[]='api/Configuration.php';
//$h->preservePath[]='api/'.$h->majorVersionPathSegmentString().'Facade.php';
//$h->preservePath[]='api/'.$h->majorVersionPathSegmentString().'endPointsClasses/';
//$h->preservePath[]='iOS/swift/models/'.$h->classPrefix.'BaseModel.swift'; // We want to be able to implement the common shared behavior


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