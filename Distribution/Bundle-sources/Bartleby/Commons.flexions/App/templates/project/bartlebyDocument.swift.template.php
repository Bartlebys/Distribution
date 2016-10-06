<?php
/**
 * Injects the variable and injects the module template.
 */
include FLEXIONS_SOURCE_DIR.'/Shared.php';

// Configuration
require_once FLEXIONS_MODULES_DIR . 'Bartleby/templates/project/SwiftDocumentConfigurator.php';
$configurator=new SwiftDocumentConfigurator();
$configurator->filename="BartlebyDocument.swift";
$configurator->includeCollectionControllerForEntityContainingString=array("User","Group","Permission","Operation","Locker");
$configurator->excludeCollectionControllerForEntityContainingString=array();

// Invocation
include FLEXIONS_MODULES_DIR . 'Bartleby/templates/project/document.swift.template.php';