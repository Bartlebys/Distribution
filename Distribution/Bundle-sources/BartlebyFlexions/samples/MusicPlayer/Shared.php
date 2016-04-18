<?php

// DEFINE IN THIS FILE ANY SHARED GLOBAL SETUPS
// THIS FILE SHOULD BE INCLUDED IN YOUR TEMPLATES


$prefix = "MusicPlayer";

/* @var $f Flexed */
if (isset ( $f )) {
	$f->package = "Models/";
	$f->company = "MusicPlayer";
	$f->prefix = $prefix;
	$f->author = "benoit@chaosmos.fr";
	$f->projectName = "MusicPlayer";
	$f->license = FLEXIONS_MODULES_DIR."Licenses/LGPL.template.php";
}

$parentClass = "WattModel";
$collectionParentClass="WattCollectionOfModel";
$protocols="WattCoding,WattCopying,WattExtraction";
$imports = "\n#import \"$parentClass.h\"\n";
$markAsDynamic = false;
$allowScalars = true;
