<?php

/*
 *  Entities (or Object) are the hart of the generation process.
 *  entities -> { properties, metadata (to define upper level behaviour - e.g: actions ) }
 *
 *  Projects define the templates to run on entities definitions (multi passes loops)
 *  projects -> { infos, templates, entities definitions (is implicit we use the definitions folder),variables, postprocessor }
 *
 *  Bunches are facilities to run a set of templates on a set of entity definitions
 *  bunch -> { infos, templates, entities definitions (paths), export path , variables }
 *
 *  Configuration files group projects and bunches to run multiple targets in one call and define runtime variables like export paths.
 *  configuration -> [ {project,state,variables} ], [ {bunch,variables} ] ]
 *
*/

/////////////////////////////////
// Flexions Configuration files
/////////////////////////////////

if (!defined('FLEXIONS_CONFIGURATION_TARGETS_KEY')) {
    define('FLEXIONS_CONFIGURATION_TARGETS_KEY','targets');
    define('FLEXIONS_CONFIGURATION_PROJECT_KEY','project');
    define('FLEXIONS_CONFIGURATION_BUNCH_KEY','bunch');
    define('FLEXIONS_CONFIGURATION_STAGE','stage');
    define('FLEXIONS_CONFIGURATION_VARIABLES','variables');
}

////////////////////////////////////
// BJM == Bartleby's Json Modeling
////////////////////////////////////

// Flexions 2.0 parser was based on Swagger 2.0.
// @Todo converge as much as possible with http://json-schema.org

if (!defined('BJM_INFOS')) {

    // Root element is a project or a Bunch.
    define('BJM_PROJECT', 'project');

    define('BJM_BUNCH', 'bunch');
        define('BJM_BUNCH_EXPORT_PATH_CONSTANT','exportPathVariableName');
        define('BJM_BUNCH_FLAT_EXPORT','flatExport');

    define('BJM_INFOS', 'infos');
        define('BJM_PROJECT_NAME', 'projectName');
        define('BJM_COMPANY', 'company');
        define('BJM_AUTHOR', 'author');
        define('BJM_YEAR', 'year');
        define('BJM_VERSION', 'version');

    define('BJM_HOST', 'host');
    define('BJM_BASE_PATH', 'basePath');
    define('BJM_TAGS', 'tags');
    define('BJM_SCHEMES', 'schemes');
    define('BJM_PATHS', 'paths');
    define('BJM_PATH', 'path');
    define('BJM_DEFINITION', 'definition');
    define('BJM_DEFINITIONS', 'definitions');
    define('BJM_EXTERNAL_DOCS', 'externalDocs');
    define('BJM_TYPE', 'type');
    define('BJM_ENUM', 'enum');
    define('BJM_OBJECT', 'object');
    define('BJM_PROPERTIES', 'properties');
    define('BJM_DESCRIPTION', 'description');
    define('BJM_FORMAT', 'format');
    define('BJM_ITEMS', 'items');
    define('BJM_REF', '$ref');
    define('BJM_DEFAULT', 'default');
    define('BJM_ALL_OF', 'allOf'); // Composition
    define('BJM_OPERATION_ID', 'operationId');
    define('BJM_PARAMETERS', 'parameters');
    define('BJM_NAME', 'name');
    define('BJM_SCHEMA', 'schema');
    define('BJM_REQUIRED', 'required');
    define('BJM_RESPONSES', 'responses');
    define('BJM_IN', 'in');
    define('BJM_HEADERS', 'headers');

    define('BJM_PROPERTY_SCOPE','scope');
        // For reference Purposes Currently we donnot validate the semantics.
        define('BJM_PROPERTY_SCOPE_PUBLIC','public');
        define('BJM_PROPERTY_SCOPE_PROTECTED','protected');
        define('BJM_PROPERTY_SCOPE_PRIVATE','private');
    define('BJS_PROPERTY_METHOD','method');
        // For reference Purposes Currently we donnot validate the semantics.
        define('BJS_PROPERTY_METHOD_CLASS','class');
        define('BJS_PROPERTY_METHOD_INSTANCE','instance');
    define('BJS_PROPERTY_MUTABILITY','mutable');
        // For reference Purposes Currently we donnot validate the semantics.
        define('BJS_PROPERTY_MUTABILITY_VARIABLE','variable');
        define('BJS_PROPERTY_MUTABILITY_CONSTANT','constant');


    define('BJM_LOOP_TEMPLATES', 'templates');
    define('BJM_LOOP_PROJECT', 'project');
    define('BJM_LOOP_ENTITIES', 'entities');
    define('BJM_LOOP_ACTIONS', 'actions');
    define('BJM_LOOP_PATHS', 'paths');

    define('BJM_VARIABLES', 'variables');
    define('BJM_POST_PROCESSOR','postProcessor');
    define('BJM_INSTANCE_OF', 'instanceOf');
    define('BJM_IS_DYNAMIC', 'dynamic');
    define('BJM_SERIALIZABLE', 'serializable');
    define('BJM_SUPERVISABLE', 'supervisable');
    define('BJM_CRYPTABLE', 'cryptable');  // Crypted on serialization
    define('BJM_ENUM_PRECISE_TYPE', 'emumPreciseType');
    define('BJM_EXPLICIT_TYPE', 'explicitType'); // used to pass an explicit type we use the explicit value literally
    define('BJM_METADATA', 'metadata');
}

if (!defined('COLLECTION_OF')) {
    define("COLLECTION_OF", "CollectionOf");
}

//////////////////////
// ENTITIES METADATA
//////////////////////

if (!defined('DEFAULT_USE_URD_MODE')) {

    // ACTIONS
    define('METADATA_KEY_FOR_USE_URD_MODE', 'urdMode');
    define('METADATA_KEY_FOR_IS_UNDOABLE', 'undoable');

    // ENTITY PERSISTENCTY IN COLLECTION CONTROLLERS.
    define('METADATA_KEY_FOR_PERSISTS_LOCALLY_ONLY_IN_MEMORY', 'persistsLocallyOnlyInMemory'); // search 'shouldPersistsLocallyOnlyInMemory'
    define('METADATA_KEY_FOR_DISTANT_PERSISTENCY_IS_ALLOWED', 'persistsDistantly'); // search 'isDistantPersistencyOfCollectionAllowed'

    // Auto Commit mecanisms.
    define('METADATA_KEY_FOR_CAN_BE_GROUPED_ON_COMMIT', 'groupable');

    // * Default Values * /
    define('DEFAULT_USE_URD_MODE', false);
    define('DEFAULT_IS_UNDOABLE', true);
    define('DEFAULT_PERSISTS_LOCALLY_ONLY_IN_MEMORY', false);
    define('DEFAULT_CAN_BE_GROUPED_ON_COMMIT', true);
    define('DEFAULT_DISTANT_PERSISTENCY_IS_ALLOWED', true);

    // To be DEPRECATED
    // but still used by XCDDataXMLToFlexionsRepresentation
    define('DEFAULT_GENERATE_COLLECTION_CLASSES', false);
}


////////////////////
// BEHAVIOURS
////////////////////

if(!defined('VERBOSE_FLEXIONS')){
    define('VERBOSE_FLEXIONS',true);
}

if (!defined('ECHO_LOGS')){
    define('ECHO_LOGS',true);
}

////////////
// Paths
////////////

if (!defined('FLEXIONS_ROOT_DIR')){
    define('FLEXIONS_ROOT_DIR', dirname(dirname(__DIR__)));
    define('FLEXIONS_MODULES_DIR', FLEXIONS_ROOT_DIR . '/modules');
    define('BARTLEBYS_MODULE_DIR', FLEXIONS_MODULES_DIR . '/Bartleby');
}