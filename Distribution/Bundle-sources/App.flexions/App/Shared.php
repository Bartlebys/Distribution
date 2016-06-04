<?php

// DEFINE IN THIS FILE ANY SHARED GLOBAL SETUPS
// THIS FILE SHOULD BE INCLUDED IN YOUR TEMPLATES

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR . 'Utils/Pluralization.php';
include_once dirname(dirname(__DIR__)). '/GenerativeConstants.php';

$isIncludeInBartlebysCommons=false;

$prefix = "";// No prefix
$modelsShouldConformToNSCoding=false; // (!) you can opt for NSCoding support (the model will not be pure swift models)
$excludeEntitiesWith=array("AbstractContext");//
$xOSIncludeCollectionControllerForEntityNamed=array();
$excludeActionsWith=array("LinkedDocument","Asset","AbstractContext","Reference","Tag","Scene","Shot","Sentence","TextPart","Sign","StringAttribute","TimeCode","TimeRange","MovieCharacter","Actor");//We generate only the entity
$unDeletableEntitiesWith=array();
$unModifiableEntitiesWith=array();
$doNotGenerate=array();


if (isset ( $f )) {
	$f->company = "LyLo Media group";
	$f->prefix = $prefix;
	$f->author = "benoit@pereira-da-silva.com";
	$f->projectName = "YouDub";
	//$f->license = FLEXIONS_MODULES_DIR."Licenses/LGPL.template.php";
}


/*
$parentClass = "";
$collectionParentClass="";
$protocols="";
$imports = "\n#import \"$parentClass.h\"\n"; // NOT NEEDED FOR SWIFT
$markAsDynamic = false;
$allowScalars = true;
*/
