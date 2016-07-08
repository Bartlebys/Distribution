<?php


$arguments=array();
$arguments['source']="./";
$arguments['destination']="out.flexions/";
$arguments['descriptor']="datasources/bartleby.json";
$arguments['templates']="*";
$arguments['preProcessors']="pre-processor.php";
$arguments['postProcessors']="post-processor.php";

define ( "COMMANDLINE_MODE", true );

// Invoke Flexions
include_once dirname(dirname(dirname(__DIR__))).'/BartlebyFlexions/src/flexions.php';

