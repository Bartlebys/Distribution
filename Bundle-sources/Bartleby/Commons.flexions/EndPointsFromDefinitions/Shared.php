<?php


// DEFINE IN THIS FILE ANY SHARED GLOBAL SETUPS
// THIS FILE SHOULD BE INCLUDED IN YOUR TEMPLATES

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR . 'Utils/Pluralization.php';
include_once dirname(dirname(dirname(__DIR__))) . '/GenerativeConstants.php';

$prefix = "Swagger";
$excludeEntitiesWith = array("AbstractContext");//

$excludeActionsWith = [
    "Asset",
    "Datum",
    "AbstractContext",
    "Reference",
    "Tag",
    "Scene",
    "Shot",
    "Sentence",
    "TextPart",
    "Sign",
    "StringAttribute",
    "TimeCode",
    "TimeRange",
    "MovieCharacter",
    "Actor"
];//We generate only the entity
$unDeletableEntitiesWith = [];
$unModifiableEntitiesWith = [];
$doNotGenerate = ["AbstractContext"];// Used by the flexions script to reject a flexed content

if (isset ($f)) {
    $f->package = "Models/";
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