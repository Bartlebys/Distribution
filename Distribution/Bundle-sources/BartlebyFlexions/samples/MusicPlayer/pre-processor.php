<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR.'XcDataModelXMLImporter/XcdatamodelXMLToFlexionsRepresentation.php';
require_once FLEXIONS_MODULES_DIR . 'XcDataModelXMLImporter/XcdataModelDelegate.php';

include  FLEXIONS_SOURCE_DIR.'/Shared.php';// we load the shared variables

// we instanciate the Hypotypose singleton
$h = Hypotypose::instance();
// In this sample we do neither stage nor seta version
// The generated out put will be directly in a folder
$h->stage=DefaultStages::NO_STAGE;
$h->version='';
$h->classPrefix=$prefix;

$transformer=new XCDDataXMLToFlexionsRepresentation();
$delegate=new XcdataModelDelegate();
$r=$transformer->projectRepresentationFromXcodeModel($descriptorFilePath,$prefix,$delegate);

/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}