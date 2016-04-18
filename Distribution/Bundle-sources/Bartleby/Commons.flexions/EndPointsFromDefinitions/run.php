<?php

$arguments=array();
$arguments['source']="./";
$arguments['destination']="out.flexions/";
$arguments['descriptor']= dirname(__DIR__) . '/App/datasources/bartleby.json';
$arguments['templates']="*";
$arguments['preProcessors']="pre-processor.php";
$arguments['postProcessors']="post-processor.php";

define ( "COMMANDLINE_MODE", true );

// Invoke flexions
include_once dirname(dirname(dirname(__DIR__))).'/BartlebyFlexions/src/flexions.php';
