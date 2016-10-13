<?php

$srcPath=dirname(dirname(__DIR__)).'/src';
require_once $srcPath.'/flexions/core/DiscreetFlexer.php';

// Mandatory Parameters
$templatePath =  $srcPath.'/modules/Bartleby/templates/entities/model.swift.template.php';
$entityDescriptionPath = __DIR__ . '/Organism.json';
$destinationPath = __DIR__.'/_generated/';

// Optional parameters.
// and call directly : DiscreetFlexer::buildDiscreet($templatePath, $entityDescriptionPath, $destinationPath)

$version = '1.0';
$projectName = 'DiscreetTest';
$company = 'Chaosmos';
$author = 'bpds';
$year = '2016';
$deleteTempFiles = false; // set to false to keep the derivated files.
$variables = array();

// Invoke the discreet generator
DiscreetFlexer::buildDiscreet($templatePath, $entityDescriptionPath, $destinationPath, $version, $projectName, $company, $author, $year, $deleteTempFiles, $variables);

//
// NOTES :
//
//  - You can group entities in a "/definitions" folder if you need HyperLinking, or advanced features.
//  - You can discreetly generate multiple entities if you group them in the "/definitions" folder.
//  - Variables are fully supported so you can exlude entities,
//  - You can use any Bunch level supported feature because discreet generation relies on temporary derivated bunch.