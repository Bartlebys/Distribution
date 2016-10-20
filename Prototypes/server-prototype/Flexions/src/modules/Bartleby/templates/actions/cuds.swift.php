<?php

include FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';

/*
 * SWIFT 3.X template
 * This weak logic template is compliant with Bartleby 1.0 approach.
 * It allows to update easily very complex templates.gt
 * It is not logic less but the logic intent to be as weak as possible
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

if (isset($f, $d, $h)) {

    /* @var $f Flexed */
    /* @var $d ActionRepresentation */
    /* @var $h Hypotypose */

    // We use explicit name (!)
    // And reserve $f , $d , $h possibly for blocks

    $flexed = $f;
    $actionRepresentation = $d;
    $hypotypose = $h;

    $flexed->fileName = $actionRepresentation->class . '.swift';
    $flexed->package = 'xOS/operations/';

} else {
    return NULL;
}

/* @var $flexed Flexed */
/* @var $actionRepresentation ActionRepresentation */
/* @var $hypotypose Hypotypose */


/////////////////
// EXCLUSIONS
/////////////////

// Should this Action be excluded ?

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}

// This template cannot be used for GET Methods
// We use  endpoint.swift.template.php
if ($actionRepresentation->httpMethod === 'GET') {
    return NULL;
}

// We want also to exclude by query
// We use  endpoint.swift.template.php
if (!(strpos($d->class, 'ByQuery') === false)) {
    return NULL;
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

// Compute ALL the Variables you need in the template
// Including generative blocks.


// Include block
$includeBlock = '';
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons == true) {
    $includeBlock .= stringIndent("import Alamofire", 1);
    $includeBlock .= stringIndent("import ObjectMapper", 1);
} else {
    $includeBlock .= stringIndent("import Alamofire", 1);
    $includeBlock .= stringIndent("import ObjectMapper", 1);
    $includeBlock .= stringIndent("import BartlebyKit", 1);
}


$httpMethod = $actionRepresentation->httpMethod;
$pluralizedName = lcfirst($actionRepresentation->collectionName);
$singularName = lcfirst(Pluralization::singularize($pluralizedName));
$baseClassName = ucfirst($actionRepresentation->class);
$ucfSingularName = ucfirst($singularName);
$ucfPluralizedName = ucfirst($pluralizedName);

$actionString = NULL;
$localAction = NULL;

$registrySyntagm = 'inRegistryWithUID';
if ($httpMethod == "POST") {
    $actionString = 'creation';
    $localAction = 'upsert';
} elseif ($httpMethod == "PUT") {
    $actionString = 'update';
    $localAction = 'upsert';
} elseif ($httpMethod == "PATCH") {
    $actionString = 'update';
    $localAction = 'upsert';
} elseif ($httpMethod == "DELETE") {
    $actionString = 'deleteByIds';
    $localAction = 'deleteByIds';
    $registrySyntagm = 'fromRegistryWithUID';
} else {
    $actionString = 'NO_FOUND';
    $localAction = 'NO_FOUND';
}

$firstParameterName = NULL;
$firstParameterTypeString = NULL;
$varName = NULL;
$executeArgumentSerializationBlock = NULL;
/* @var $firstParameter PropertyRepresentation */
$firstParameter = NULL;
$handlesCollection=false;

while ($actionRepresentation->iterateOnParameters()) {
    /*@var $parameter PropertyRepresentation*/
    $parameter = $actionRepresentation->getParameter();
    // We use the first parameter.

    if (!isset($varName, $firstParameterName, $firstParameterTypeString)) {
        $firstParameter = $parameter;
        $firstParameterName = $parameter->name;
        $handlesCollection=($firstParameter->type == FlexionsTypes::COLLECTION);
    }
}

// Note on deletion.
// The swift implementation uses full object (let's User)
// When the endPoints use ids.
// So we need to remap variable to reflect this non symetric situation.
if ($httpMethod != 'DELETE'){
    $privateMemberName = '_' . $firstParameterName;
    $subjectName = $firstParameterName;
    $subjectStringType = $handlesCollection ? "[$firstParameter->instanceOf]" : $firstParameter->instanceOf;
    $subjectUnitaryType = $firstParameter->instanceOf;
}else{
    $privateMemberName = '_' .($handlesCollection ? $pluralizedName : $singularName);
    $subjectName = $handlesCollection ? $pluralizedName : $singularName;
    $subjectStringType = $handlesCollection ? "[$ucfSingularName]" : $ucfSingularName;
    $subjectUnitaryType = $ucfSingularName;
}

if ($handlesCollection) {
    if ($httpMethod != 'DELETE') {
        $firstParameterTypeString = '[' . $ucfSingularName . ']';
        $executeArgumentSerializationBlock = "
                var parameters=Dictionary<String, Any>()
                var collection=[Dictionary<String, Any>]()
                for $singularName in $pluralizedName{
                    let serializedInstance=Mapper<$ucfSingularName>().toJSON($singularName)
                    collection.append(serializedInstance)
                }
                parameters[\"$pluralizedName\"]=collection" . cr();
    } else {
        $firstParameterTypeString = '[String]';
        $executeArgumentSerializationBlock = "
                var parameters=Dictionary<String, Any>()
                parameters[\"ids\"]=$subjectName.map{\$0.UID}" . cr();
    }
    $varName = $pluralizedName;
} else {
    if ($httpMethod != 'DELETE') {
        $firstParameterTypeString = $ucfSingularName;
        $executeArgumentSerializationBlock = "
                var parameters=Dictionary<String, Any>()
                parameters[\"$singularName\"]=Mapper<$firstParameterTypeString>().toJSON($firstParameterName)" . cr();
    } else {
        $firstParameterTypeString = 'String';
        $executeArgumentSerializationBlock = "
                var parameters=Dictionary<String, Any>()
                parameters[\"" . $singularName . "Id\"]=" . $subjectName . ".UID" . cr();
    }
    $varName = $singularName;
}




//////////////////////////////
//
// THIS IS A COMPLEX CASE
// READ CAREFULLY
//
// We want to serialize the parameters has Mappable & NSSecureCoding
// and  not to serialize globally the operation
// as the operation will serialize this instance in its data dictionary.
//
// We Gonna inject the relevant private properties.
// #1 Create a virtual entity
// #2 Inject the PropertyRepresentation
////////////////////////////////

/* @var $virtualEntity EntityRepresentation */

$virtualEntity = new EntityRepresentation();
$virtualEntity->name = $ucfSingularName;
$_ENTITY_rep = new PropertyRepresentation();
$_ENTITY_rep->name = $privateMemberName;
$_ENTITY_rep->type = $handlesCollection ? FlexionsTypes::COLLECTION: FlexionsTypes::OBJECT;
$_ENTITY_rep->instanceOf = $subjectUnitaryType;
$_ENTITY_rep->required = true;
$_ENTITY_rep->isDynamic = false;
$_ENTITY_rep->default = NULL;
$_ENTITY_rep->isGeneratedType = true;
$virtualEntity->properties[] = $_ENTITY_rep;


$_spaceUID_rep = new PropertyRepresentation();
$_spaceUID_rep->name = "_registryUID";
$_spaceUID_rep->type = FlexionsTypes::STRING;
$_spaceUID_rep->required = true;
$_spaceUID_rep->isDynamic = false;
$_spaceUID_rep->default = "Default.NO_UID";
$_spaceUID_rep->isGeneratedType = false;
$virtualEntity->properties[] = $_spaceUID_rep;

// Acknowledgement Block
if ($parameter->type == FlexionsTypes::COLLECTION) {
    $acknowledgementBlock =
        "let acknowledgment=Acknowledgment()
acknowledgment.triggerIndex=index.intValue
acknowledgment.uids=$subjectName.map({\$0.UID})
acknowledgment.versions=$subjectName.map({\$0.version})
document.record(acknowledgment)";
} else {
    $acknowledgementBlock =
        "let acknowledgment=Acknowledgment()
acknowledgment.triggerIndex=index.intValue
acknowledgment.uids=[$subjectName.UID]
acknowledgment.versions=[$subjectName.version]
document.record(acknowledgment)";
}
$acknowledgementBlock = stringIndent($acknowledgementBlock, 10);
$acknowledgementBlock = cr() . $acknowledgementBlock;


// Operation identification block

$operationIdentificationBlock = cr();

if ($parameter->type == FlexionsTypes::COLLECTION) {
    $operationIdentificationBlock .= stringIndent('let stringIDS=PString.ltrim(self._' . $subjectName . '.reduce("", { $0+","+$1.UID }),characters:",")', 4);
    $operationIdentificationBlock .= stringIndent('operation.summary="' . $baseClassName . '(\(stringIDS))"', 4);
} else {
    $operationIdentificationBlock .= stringIndent('operation.summary="' . $baseClassName . '(\(self._' . $subjectName . '.UID))"', 4);
}

// Operation commit block
$operationCommitBlock = '';
if ($httpMethod != "DELETE") {
    $operationCommitBlock .= cr();
    if ($parameter->type == FlexionsTypes::COLLECTION) {
        $operationCommitBlock .= stringIndent("for item in self._$subjectName{", 4);
        $operationCommitBlock .= stringIndent("item.committed=true", 5);
        $operationCommitBlock .= stringIndent("}", 4);
    } else {
        $operationCommitBlock .= stringIndent("self._$firstParameterName.committed=true", 4);
    }
}
$operationCommitBlock .= cr();

// Distributed Block
$distributedBlock = '';
if ($httpMethod != "DELETE") {
    $distributedBlock .= cr();
    if ($parameter->type == FlexionsTypes::COLLECTION) {
        $distributedBlock .= stringIndent("for item in self._$subjectName{", 5);
        $distributedBlock .= stringIndent("item.distributed=true", 6);
        $distributedBlock .= stringIndent("}", 5);
    } else {
        $distributedBlock .= stringIndent("self._$subjectName.distributed=true", 5);
    }
}

// Exposed Block
$exposedBlock = '';
if ($modelsShouldConformToExposed) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false]);
    $exposedBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/Exposed.swift.block.php');
}
$mappableBlock = '';
if ($modelsShouldConformToMappable) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false, 'mappableblockEndContent' => '']);
    $mappableBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/Mappable.swift.block.php');
}
$secureCodingBlock = '';
if ($modelsShouldConformToNSSecureCoding) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false, 'decodingblockEndContent' => '', 'encodingblockEndContent' => '']);
    $secureCodingBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}


/////////////////////////
// WEAK LOGIC TEMPLATE
/////////////////////////

include __DIR__ . '/cuds.withWeakLogic.swift.template.php';