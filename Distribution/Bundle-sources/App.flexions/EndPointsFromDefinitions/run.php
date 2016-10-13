<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 09/07/15
 * Time: 14:56
 * You can call this little script from command line
 * php -f run.php
 * it is  equivalent to . globalflexions.sh
 * its main advantage is that it can be debugged directly more easily
 */

$arguments=array();
$arguments['source']="./";
$arguments['destination']="out.flexions/";
$arguments['descriptor']= dirname(__DIR__) . '/App/datasources/youdub.json';
$arguments['templates']="*";
$arguments['preProcessors']="pre-processor.php";
$arguments['postProcessors']="post-processor.php";

define ( "COMMANDLINE_MODE", true );

// Invoke flexions
include_once dirname(dirname(__DIR__)).'/BartlebyFlexions/src/flexions.php';