<?php
/**
 * Injects the variable and injects the module template.
 */
include FLEXIONS_SOURCE_DIR.'/Shared.php';

// Configuration
require_once FLEXIONS_MODULES_DIR . 'Bartleby/templates/project/SwiftDocumentConfigurator.php';
$configurator=new SwiftDocumentConfigurator();
$configurator->filename="WorkspaceDocument.swift";
$configurator->useActionsContainingString=array("Episode","User","Group","Permission","Operation","Workspace","Project","Fragment","Note","Trigger");

// Invocation
include FLEXIONS_MODULES_DIR . 'Bartleby/templates/project/document.swift.template.php';