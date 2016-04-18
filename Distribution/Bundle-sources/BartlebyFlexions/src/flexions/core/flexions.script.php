<?php
/*

Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.chaosmos.fr

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/Licenses/>

*/

require_once FLEXIONS_ROOT_DIR . 'flexions/Core/Flog.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/Core/Hypotypose.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/Core/Flexed.php';
require_once FLEXIONS_ROOT_DIR.  'flexions/Core/functions.script.php';
require_once FLEXIONS_ROOT_DIR.  'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set ( 'UTC' );

// The argument can also be defined from a boot php script
if (! isset($arguments)) {
	// Server & commandline versatile support
	if ($_SERVER ['argc'] == 0 || !defined('STDIN')) {
		// Server mode
		$arguments = $_GET;
		define("COMMANDLINE_MODE", false);
	} else {
		// Command line mode
		$rawArgs = $_SERVER ['argv'];
		array_shift($rawArgs); // shifts the commandline script file flexions.php
		$arguments = array();
		parse_str(implode('&', $rawArgs), $arguments);
		define("COMMANDLINE_MODE", true);
	}
}

// We instanciate the Flog singleton
// and store a time stamp as  first log.
Flog::Instance ()->addMessage ( '##' . fDate () . '##' . cr() );
                                        
$preProcessors = '';
$postProcessors = '';
$source = '';
$descriptorFilePath='';
$destination='';
if (isset ( $arguments ["source"] )) {
	$source = $arguments ["source"];
} else {
	 throw new Exception ( 'Required parameter "source"' . cr() );
}

define ( "FLEXIONS_SOURCE_DIR", $source );

if (isset ( $arguments ["descriptor"] )) {
	if(file_exists($arguments["descriptor"])){
		// We use an absolute path
		$descriptorFilePath=$arguments["descriptor"];
	}else{
		// we use a relative path
		$descriptorFilePath = FLEXIONS_SOURCE_DIR  . $arguments ["descriptor"];
	}
}



if (isset ( $arguments ["templates"] ) && strlen ( $arguments ["templates"] ) >= 1) {
	$templates = $arguments ["templates"];
} else {
	$templates = '*';
}

if (isset ( $arguments ["destination"] ) && strlen ( $arguments ["destination"] ) >= 1) {
	$destination = injectVersionInPath($arguments ["destination"]);
} else {
	$destination = injectVersionInPath(FLEXIONS_ROOT_DIR . '/out/standard/');
	if(file_exists($destination)==false)
		mkdir ( $destination, 0777, true );
}

if (isset ( $arguments ["preProcessors"] ))
	$preProcessors = $arguments ["preProcessors"];

if (isset ( $arguments ["postProcessors"] ))
	$postProcessors = $arguments ["postProcessors"];
	
	// Check if mandatory $arguments are set
	// (Preprocessors and PostProcessors are optionnal)

if (! isset ( $descriptorFilePath )) {
	 throw new Exception( 'Required parameter "descriptor"' . cr() );
}


$baseTemplatePath = FLEXIONS_SOURCE_DIR . 'templates';
// Templates joker.
if ($templates == "*") {
	// We populate the templates with the relative path
	$templatesArray = directoryToArray ( $baseTemplatePath );
	$templates = implode ( ',', $templatesArray );
}else{
	$templatesTempArray= explode(',', $templates);
	$templatesArray=array();
	foreach ( $templatesTempArray as $templatePath ) {
		// Compute the absolute path
		$templatesArray[]=$baseTemplatePath."/".$templatePath;;
	}
}


$specificLoops = FLEXIONS_SOURCE_DIR . 'loops.php';

$m = cr();
$m.=  '# phpversion : '.phpversion().cr();
$m .= '# Invoking flexions  for ' .simplifyPath($descriptorFilePath) . cr();
$m .= '# On template(s): ' . str_replace ( FLEXIONS_SOURCE_DIR . 'templates/', '', $templates ) . cr();
$m .= '# With destination: ' . simplifyPath($destination) . cr();
$m .= '# Using pre-processor: ' . $preProcessors . cr();
$m .= '# And post-processor: ' . $postProcessors . cr();
if (file_exists ( $specificLoops )) {
	$m .= '# Using the loops : ' . $source . '/loops.php' . cr() . cr();
}else{
	$m .=  '# Using the standard loops' . cr() . cr() ;
}
$m .='FLEXIONS_SOURCE_DIR='. FLEXIONS_SOURCE_DIR.cr();
$m .='FLEXIONS_ROOT_DIR='. FLEXIONS_ROOT_DIR;
fLog ( $m, true );

// /////////////////////////////////
// PHASE #1
// PREPROCESSING
// /////////////////////////////////

fLog ( cr().cr().'##'.cr(), true );
fLog ( 'Pre Processing'.cr(), true );
fLog ( '##'.cr().cr(),true );


$arrayOfPreProcessors = explode ( ",", $preProcessors );
foreach ( $arrayOfPreProcessors as $preProcessor ) {
	// Invokes the pre-processor
	$preProcessorPath = FLEXIONS_SOURCE_DIR . $preProcessor;
    fLog ( cr().'Running:'.$preProcessorPath.cr().cr(),true );
	try {
		include $preProcessorPath;
	}catch (Exception $e) {
		fLog('PREPROCESSOR EXCEPTION ' . $e->getMessage(),true);
        dumpLogs();
		return;
	}
}
	
// //////////////////////////////////
// PHASE #2
// PROCESSING
// /////////////////////////////////

/**
 *
 * @var $h Hypotypose
 *     
 */

$destination=$destination.$h->majorVersionPathSegmentString().$h->stagePathSegmentString();
$h=Hypotypose::Instance();
$h->exportFolderPath=$destination;

fLog ( cr().cr().'##'.cr(), true );
fLog ( 'Looping'.cr(), true );
fLog ( '##'.cr().cr(), true );

if (file_exists ( $specificLoops )) {
	// We use the specific loops
	include $specificLoops;
} else {
	
	while ( $h->nextLoop () == true ) {
		$list = $h->getContentForCurrentLoop (); // Returns the current loop items
		fLog ( 'Looping in '.$h->getLoopName().cr(), true );

		foreach ( $list as $descriptions ) {


            try {// It is a description object
                iterateOnTemplates($templatesArray, $h, $descriptions, $destination);
            } catch (Exception $e) {
                fLog('TEMPLATE EXCEPTION ' . $e->getMessage(),true);
                dumpLogs();
                return;
            }
		}
	}
}

/**
 * 
 * @param array $templatePath
 * @param Hypotypose $h
 * @param mixed $d the descriptor (a set of data)
 * @throws Exception
 */
 function iterateOnTemplates(array $templatesArray, Hypotypose $h,  $d,$destination){
 	
	foreach ( $templatesArray as $templatePath ) {
			
		// We need to determine if the template should be used in
		// this loop.
        $loopName=$h->getLoopName ();
		$componentsOfTemplatePath=explode('/',strtolower($templatePath));

        $shouldBeUsedInThisLoop = in_array(strtolower($loopName),$componentsOfTemplatePath);
			
		if ($shouldBeUsedInThisLoop) {
	
			$result = NULL;
			if (! isset ( $d )) {
				throw new Exception( 'Descriptor variable $d must be set for the templates. Your preprocessor should have populated an iterable list of data for the descriptor for the loop : '.$loopName );
			}

			// We instanciate the current Flexed
			// will be used by the templates to define $f->fileName, $f->package
			$f = new Flexed ($h->classPrefix);

			// ( ! ) Template execution
			ob_start ();include $templatePath;$result = ob_get_clean ();
			// (!) End of template execution
	
			if ($f->fileName != null ) {
					
				$f->source = $result; // We store the generation result
				//and the package path
				$f->packagePath = $destination . $f->package;
				 
				// We add the flexed the Hypotypose for the post processors
				$h-> addFlexed($f);
                fLog ( '+Adding '.$f->fileName.cr(), true );
			} else {
				fWarning ( 'fileName or package is not defined in ' . $templatePath );
			}
		}
	}
}


// //////////////////////////////////
// PHASE #3
// POST PROCESSING
// ///////////////////////////////////

fLog ( cr().cr().'##'.cr(), true );
fLog ( 'Post Processing'.cr(), true );
fLog ( '##'.cr().cr(),true );
$arrayOfPostProcessors = explode ( ",", $postProcessors );
foreach ( $arrayOfPostProcessors as $postProcessor ) {
	// Invokes the post-processor
	$postProcessorPath = FLEXIONS_SOURCE_DIR  . $postProcessor;
    fLog ( cr().'Running:'.$postProcessorPath.cr().cr(),true );
    try {
        include $postProcessorPath;
    }catch (Exception $e) {
        fLog('POSTPROCESSOR EXCEPTION ' . $e->getMessage(),true);
        dumpLogs();
        return;
    }
}
dumpLogs();



function dumpLogs(){
    // Dump Flog

    $logFolderPath = FLEXIONS_ROOT_DIR . '../out/logs/';
    if(! file_exists($logFolderPath)){
        mkdir ( $logFolderPath, 0777, true );
    }
    $logsFilePath = $logFolderPath . fDate () . '-logs.txt';
    file_put_contents ( $logsFilePath, Flog::Instance ()->getLogs () );
}


