<?php

// Flexions 3.0 bindings
// Injects the variables to the flexions 2.0 templates the local scope.
// And sets default value if not set.

$h = Hypotypose::Instance();
$registry = Registry::Instance();

$modelsShouldConformToNSCoding = $registry->valueForKey('modelsShouldConformToNSCoding');
$excludeEntitiesWith = $registry->valueForKey('excludeEntitiesWith');
$xOSIncludeCollectionControllerForEntityNamed = $registry->valueForKey('xOSIncludeCollectionControllerForEntityNamed');
$excludeActionsWith = $registry->valueForKey('excludeActionsWith');
$excludeFromServerActionsWith = $registry->valueForKey('excludeFromServerActionsWith');
$unDeletableEntitiesWith = $registry->valueForKey('unDeletableEntitiesWith');
$unModifiableEntitiesWith = $registry->valueForKey('unModifiableEntitiesWith');
$doNotGenerate = $registry->valueForKey('doNotGenerate');
$isIncludeInBartlebysCommons = $registry->valueForKey('isIncludeInBartlebysCommons');
$configurator = $registry->valueForKey('configurator');

if (!isset($modelsShouldConformToNSCoding)) {
    $modelsShouldConformToNSCoding = true;
}
if (!isset($isIncludeInBartlebysCommons)) {
    $isIncludeInBartlebysCommons = false;
}