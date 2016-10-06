<?php

// DEFINE IN THIS FILE ANY SHARED GLOBAL SETUPS
// THIS FILE SHOULD BE INCLUDED IN YOUR TEMPLATES

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR . 'Utils/Pluralization.php';
include_once dirname(dirname(dirname(__DIR__))) . '/GenerativeConstants.php';

$isIncludeInBartlebysCommons = true;
$prefix = "";// No prefix


$modelsShouldConformToNSCoding = true; // (!) you can opt for NSCoding support (the model will not be pure swift models)

$excludeEntitiesWith = ["AbstractContext"]; //

$xOSIncludeCollectionControllerForEntityNamed = [
    "Operation"
];

$excludeActionsWith = [
    "JString", "JDictionary", "JData", // Primitive Wrapper
    "Trigger",
    "Operation",
    "Abstract", // Any abstract entity should be ignored
    "ExternalReference",
    "Progression",
    "Completion",
    "BaseObject",
    "Tag",
    "CollectionMetadatum",
    "HTTPResponse",
    "RegistryMetadata",
    "CollectionMetadata",
    "CollectionMetadatum"//

];//We will generate only the entity ( On client and server side)
$excludeFromServerActionsWith = [""];

$unDeletableEntitiesWith = [];
$unModifiableEntitiesWith = [];
$doNotGenerate = [];

if (isset ($f)) {
    $f->company = "Bartleby's | https://bartlebys.org";
    $f->prefix = $prefix;
    $f->author = "b@bartlebys.org";
    $f->projectName = "Bartleby";
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
