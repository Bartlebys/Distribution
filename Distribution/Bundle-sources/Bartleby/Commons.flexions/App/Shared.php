<?php

// DEFINE IN THIS FILE ANY SHARED GLOBAL SETUPS
// THIS FILE SHOULD BE INCLUDED IN YOUR TEMPLATES

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR . 'Utils/Pluralization.php';
include_once dirname(dirname(dirname(__DIR__))) . '/GenerativeConstants.php';

$isIncludeInBartlebysCommons = true;

$prefix = "";// No prefix

$modelsShouldConformToNSCoding = true; // (!) you can opt for NSCoding support (the model will not be pure swift models)

$excludeEntitiesWith = array("AbstractContext"); //

$xOSIncludeCollectionControllerForEntityNamed = array("Operation");

$excludeActionsWith = array(
                                "BaseObject",
                                "ObjectError",
                                "Tag",
                                "CollectionMetadatum",
                                "HTTPResponse",
                                "BaseRegistryMetadata",
                                "BaseCollectionMetadata",
                                "BaseCollectionMetadatum"//

                            );//We will generate only the entity ( On client and server side)
$excludeFromServerActionsWith = array("");

$unDeletableEntitiesWith = array();
$unModifiableEntitiesWith = array();

$doNotGenerate = array("BaseObject.php","ObjectError.php","BaseRegistryMetadata.php","BaseCollectionMetadatum.php");

if (isset ($f)) {
    $f->company = "Chaosmos | https://chaosmos.fr";
    $f->prefix = $prefix;
    $f->author = "benoit@pereira-da-silva.com";
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
