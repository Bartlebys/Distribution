<?php

require_once __DIR__ . '/FlexersConsts.php';
require_once __DIR__ . '/Registry.php';
require_once BARTLEBYS_MODULE_DIR . '/templates/Requires.php';
require_once FLEXIONS_ROOT_DIR . '/flexions/core/functions.script.php';
require_once FLEXIONS_MODULES_DIR . '/Utils/Pluralization.php';


/**
 * Class BJMParser
 * BJM == Bartleby's Json Modeling
 * Flexions 2.0 parser was based on Swagger 2.0.
 * @Todo converge as much as possible with http://json-schema.org
 */
class BJMParser {

    /* @var MetaFlexer */
    protected $_metaFlexer;

    /**
     * Flexer constructor.
     * @param MetaFlexer $_metaFlexer
     */
    public function __construct(MetaFlexer $_metaFlexer) {
        $this->_metaFlexer = $_metaFlexer;
    }

    /**
     * Returns a project Representation from the App Descriptor json file
     * @param string $jsonFilePath the App Descriptor json file
     * @return ProjectRepresentation the project
     */
    public function jsonToProjectRepresentation($jsonFilePath) {

        $nativePrefix = '';
        $signInSignature = array();
        $signOutSignature = array();

        // descriptors/_derivated/_global.json ->  descriptors/definitions/*.json
        $definitionsFolder = dirname(dirname($jsonFilePath)) . '/definitions';

        $project = new ProjectRepresentation();
        $globalDescriptorArray = $this->_arrayFromPath($jsonFilePath);
        if (!is_array($globalDescriptorArray)) {
            throw  new Exception('Global descriptor is not an associatieve array');
        }

        /////////////
        // GLOBALS
        /////////////

        // This mecanism replaces flexions the Shared.php file used in run session
        // To define global variables
        if (array_key_exists(BJM_VARIABLES, $globalDescriptorArray)) {
            $variables = $globalDescriptorArray[BJM_VARIABLES];
            Registry::Instance()->defineVariables($variables);
        }

        /////////////
        // PROJECT
        /////////////

        if (array_key_exists(BJM_LOOP_TEMPLATES, $globalDescriptorArray)) {
            $this->_metaFlexer->setTemplates($globalDescriptorArray[BJM_LOOP_TEMPLATES]);
        } else {
            throw  new Exception('Loops templates not found "root.' . BJM_LOOP_TEMPLATES . '"');
        }

        $project->classPrefix = $nativePrefix;
        $project->name = $this->_metaFlexer->projectName;
        $project->metadata = $globalDescriptorArray;// We store the raw descriptor as a metadata

        if (array_key_exists(BJM_INFOS, $globalDescriptorArray)) {
            $infosDictionary = $globalDescriptorArray[BJM_INFOS];
            if (is_array($infosDictionary)) {
                if (array_key_exists(BJM_VERSION, $infosDictionary)) {
                    $this->_metaFlexer->version = $infosDictionary[BJM_VERSION];
                }
                if (array_key_exists(BJM_PROJECT_NAME, $infosDictionary)) {
                    $this->_metaFlexer->projectName = $infosDictionary[BJM_PROJECT_NAME];
                }
                if (array_key_exists(BJM_COMPANY, $infosDictionary)) {
                    $this->_metaFlexer->company = $infosDictionary[BJM_COMPANY];
                }
                if (array_key_exists(BJM_AUTHOR, $infosDictionary)) {
                    $this->_metaFlexer->author = $infosDictionary[BJM_AUTHOR];
                }
                if (array_key_exists(BJM_YEAR, $infosDictionary)) {
                    $this->_metaFlexer->year = $infosDictionary[BJM_YEAR];
                }
            }
        }

        $project->baseUrl = $globalDescriptorArray[BJM_SCHEMES][0] . '://' . $globalDescriptorArray[BJM_HOST] . $globalDescriptorArray[BJM_BASE_PATH];
        $project->apiVersion = rtrim($globalDescriptorArray[BJM_BASE_PATH], '/');


        /////////////
        // ENTITIES
        /////////////

        if (array_key_exists(BJM_DEFINITIONS, $globalDescriptorArray)) {
            $definitions = $globalDescriptorArray[BJM_DEFINITIONS];
            if (is_array($definitions)) {
                foreach ($definitions as $name => $definition) {
                    $project->entities[] = $this->entityFromDefinition($name, $definition, $definitionsFolder);
                }
            } else {
                throw new Exception('Entity Definition is not an array');
            }
        } else {
            throw new Exception(BJM_DEFINITIONS . ' not found');
        }


        /////////////
        // ACTIONS
        /////////////

        if (array_key_exists(BJM_PATHS, $globalDescriptorArray)) {
            $paths = $globalDescriptorArray[BJM_PATHS];
            foreach ($paths as $path => $pathDescriptor) {
                foreach ($pathDescriptor as $method => $methodPathDescriptor) {
                    $className = '';

                    if (array_key_exists(BJM_OPERATION_ID, $methodPathDescriptor)) {
                        $className = $nativePrefix . ucfirst($methodPathDescriptor[BJM_OPERATION_ID]);
                    } else {
                        $className = $nativePrefix . $this->_classNameForPath($path);
                    }
                    $action = new ActionRepresentation();
                    $action->class = $className;
                    $action->path = $path;
                    $action->httpMethod = strtoupper($method);

                    if (array_key_exists(BJM_TAGS, $methodPathDescriptor)) {
                        $tags = $methodPathDescriptor[BJM_TAGS];
                        if (is_array($tags) && count($tags) > 0) {
                            $action->collectionName = $tags[0];
                        }
                    }

                    if (array_key_exists(BJM_PARAMETERS, $methodPathDescriptor)) {
                        $parameters = $methodPathDescriptor[BJM_PARAMETERS];
                        foreach ($parameters as $parameter) {
                            if (array_key_exists(BJM_NAME, $parameter)) {
                                $property = $this->_extractPropertyFrom($parameter[BJM_NAME], $parameter, $nativePrefix);
                                $action->parameters[] = $property;
                            }
                        }
                    }

                    if (array_key_exists(BJM_RESPONSES, $methodPathDescriptor)) {
                        $responses = $methodPathDescriptor[BJM_RESPONSES];
                        foreach ($responses as $name => $response) {
                            if ($name == "default") {
                                $name = "200";// We consider default as a succes.
                            }
                            $property = $this->_extractPropertyFrom("$name", $response, $nativePrefix);
                            $action->responses[] = $property;
                        }
                    }

                    // Action metadata
                    if (array_key_exists(BJM_METADATA, $methodPathDescriptor)) {
                        $action->metadata = $methodPathDescriptor[BJM_METADATA];
                    }
                    $project->actions[] = $action;
                }
            }
        }
        return $project;
    }


    /**
     * Returns an entity from a single entity file
     * @param string $jsonFilePath
     * @return EntityRepresentation
     * @throws Exception
     */
    public function jsonToEntityRepresentation($jsonFilePath) {
        $name = '';
        $envelop = $this->_arrayFromPath($jsonFilePath);
        if (!is_array($envelop)) {
            throw new Exception('Envelop is not an Array ' . $jsonFilePath);
        }
        $descriptionsFolder = dirname($jsonFilePath);
        if (array_key_exists('name', $envelop)) {
            $name = $envelop['name'];
        } else {
            throw new Exception('Name not found in ' . $jsonFilePath);
        }
        if (array_key_exists('definition', $envelop)) {
            $definition = $envelop['definition'];
            return $this->entityFromDefinition($name, $definition, dirname($jsonFilePath));
        } else {
            throw new Exception('Definition not found in ' . $jsonFilePath);
        }
    }

    /**
     * Returns the entity from its JSON definition
     * @param string $name
     * @param array $definition
     * @param string $definitionsFolder
     * @return EntityRepresentation
     */
    public function entityFromDefinition($name, array $definition, $definitionsFolder) {

        $nativePrefix = '';
        $descriptor = $definition;
        $e = new EntityRepresentation();
        $e->name = $name;

        if (array_key_exists(BJM_DESCRIPTION, $descriptor)) {
            $e->description = $descriptor[BJM_DESCRIPTION];
        }

        $properties = array();

        if (array_key_exists(BJM_EXPLICIT_TYPE, $descriptor)) {
            // It is an object with an explicit type
            $explicitType = $descriptor[BJM_EXPLICIT_TYPE];
            $e->instanceOf = $explicitType;
        }else{
            // it is an object.
        }

        if (array_key_exists(BJM_PROPERTIES, $descriptor)) {
            // we have a  properties key.
            $properties = array_merge($properties, $descriptor[BJM_PROPERTIES]);

        } else {
            // we use all of composition
            if (array_key_exists(BJM_ALL_OF, $descriptor)) {
                $allOF = $descriptor[BJM_ALL_OF];
                $parentRef = NULL;
                foreach ($allOF as $currentItem) {
                    if (is_array($currentItem)) {
                        if (array_key_exists(BJM_REF, $currentItem)) {
                            // COMPOSITION
                            $keyForRef = $currentItem[BJM_REF];
                            $keyForRef = str_replace("#/definitions/", "", $keyForRef);
                            $subDefinition = $this->_arrayFromPath($definitionsFolder . '/' . $keyForRef . '.json');
                            if(array_key_exists(BJM_DEFINITION,$subDefinition)){
                                $subDefinition=$subDefinition[BJM_DEFINITION];
                            }
                            //  use properties
                            if (array_key_exists(BJM_PROPERTIES, $subDefinition)) {
                                $subDefinitionProperties = $subDefinition[BJM_PROPERTIES];
                                $properties = array_merge($properties, $subDefinitionProperties);
                            }
                            if (array_key_exists(BJM_ALL_OF, $subDefinition)) {
                                $subDefinitionProperties = $subDefinition[BJM_ALL_OF];
                                $properties = array_merge($properties, $subDefinitionProperties);
                            }


                        }
                        if (array_key_exists(BJM_PROPERTIES, $currentItem)) {
                            $currentItemProperties = $currentItem[BJM_PROPERTIES];
                            $properties = array_merge($properties, $currentItemProperties);
                        }
                    }
                }

                if (array_key_exists(BJM_PROPERTIES, $allOF)) {
                    $properties = array_merge($properties, $allOF[BJM_PROPERTIES]);
                }
            }
        }
        // Parse the properties
        foreach ($properties as $propertyName => $propertyValue) {
            $e->properties[] = $this->_extractPropertyFrom($propertyName, $propertyValue, $nativePrefix);
        }

        // Entity metadata
        if (array_key_exists(BJM_METADATA, $descriptor)) {
            $e->metadata = $descriptor[BJM_METADATA];
        }
        return $e;
    }

    /**
     * @param string $prefix
     * @param string $baseClassName
     * @return string string
     */
    function getCollectionClassName($prefix, $baseClassName) {
        return ucfirst($prefix) . COLLECTION_OF . $baseClassName;
    }


    /**
     * @param $dir string the initial directory path
     * @param array $result the array of paths
     * @param string $withExtension the extension to include (if set to '' all the file are grabbed)
     * @return array the array of paths
     */
    public function arrayOfPathsFrom($dir, &$result = array(), $withExtension = '') {
        $dirList = scandir($dir);
        foreach ($dirList as $key => $value) {
            $dotPos = strpos($value, '.');
            if (($dotPos === false) or ($dotPos != 0)) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $sub = $this->arrayOfPathsFrom($dir . DIRECTORY_SEPARATOR . $value);
                    $result = array_merge($result, $sub);
                } else {
                    if ($withExtension !== '') {
                        $infos = pathinfo($value, PATHINFO_EXTENSION);
                        $ext = strtolower($infos[PATHINFO_EXTENSION]);
                        if ($ext == $withExtension) {
                            $result [] = $dir . DIRECTORY_SEPARATOR . $value;
                        }
                    } else {
                        $result [] = $dir . DIRECTORY_SEPARATOR . $value;
                    }
                }
            }
        }
        return $result;
    }


    /**
     * Resolve a path
     *
     *  - ${CONFIGURATION_FOLDER}
     *  - ${BARTLEBYS_MODULE_DIR}
     *  - ${FLEXIONS_MODULES_DIR}
     *  - ${FLEXIONS_ROOT_DIR}
     *  - ${FLEXIONS_ARGS_GENERATED_OUTPUT_PATH_KEY}
     *
     * + all the variables in the registry via ${variable_name}
     *
     * @param $path
     * @param string $configurationFolderPath
     * @return mixed
     */
    public function resolve($path,$configurationFolderPath='') {
        return BJMParser::resolvePath($path,$configurationFolderPath);
    }


    /* @var string $_lastConfigurationPath we store the last configuration path to deal efficiently with the ${CONFIGURATION_FOLDER} tag */
    private static $_lastConfigurationPath='';

    /**
     * Resolve a path
     *
     * Supports the tags :
     *
     *  - ${CONFIGURATION_FOLDER}
     *  - ${BARTLEBYS_MODULE_DIR}
     *  - ${FLEXIONS_MODULES_DIR}
     *  - ${FLEXIONS_ROOT_DIR}
     *  - ${FLEXIONS_ARGS_GENERATED_OUTPUT_PATH_KEY}
     *
     * + all the variables in the registry via ${variable_name}
     *
     *
     * @param $path
     * @param string $configurationFolderPath
     * @return mixed
     */
    public static function resolvePath($path,$configurationFolderPath='') {
        if ($configurationFolderPath!=''){
            BJMParser::$_lastConfigurationPath=$configurationFolderPath;
        }
        $resolved = $path;
        $resolved = str_replace('${CONFIGURATION_FOLDER}', BJMParser::$_lastConfigurationPath, $resolved);

        if (defined('BARTLEBYS_MODULE_DIR')) {
            $resolved = str_replace('${BARTLEBYS_MODULE_DIR}', BARTLEBYS_MODULE_DIR, $resolved);
        }
        if (defined('FLEXIONS_MODULES_DIR')) {
            $resolved = str_replace('${FLEXIONS_MODULES_DIR}', FLEXIONS_MODULES_DIR, $resolved);
        }
        if (defined('FLEXIONS_ROOT_DIR')) {
            $resolved = str_replace('${FLEXIONS_ROOT_DIR}', FLEXIONS_ROOT_DIR, $resolved);
        }
        if (defined('FLEXIONS_ARGS_GENERATED_OUTPUT_PATH_KEY')) {
            $resolved = str_replace('${FLEXIONS_ARGS_GENERATED_OUTPUT_PATH_KEY}', FLEXIONS_ARGS_GENERATED_OUTPUT_PATH_KEY, $resolved);
        }

        $variables=Registry::Instance()->getVariables();
        foreach ($variables as $name=>$value) {
            if(is_string($value)){
                $resolved = str_replace('${'.$name.'}', $value, $resolved);
            }
        }
        return $resolved;
    }


    /**
     * @param string $propertyName
     * @param $propertyValue
     * @param string $nativePrefix
     * @return PropertyRepresentation
     */
    private function _extractPropertyFrom($propertyName, $propertyValue, $nativePrefix) {
        // type, format, description
        $propertyR = new PropertyRepresentation();
        $propertyR->name = $propertyName;
        if (is_array($propertyValue)) {
            $context = $propertyValue;
            if (array_key_exists(BJM_SCHEMA, $propertyValue)) {
                // Seen in parameters.
                $context = $propertyValue[BJM_SCHEMA];
            }
            // Most common
            $this->_parsePropertyType($propertyR, $context, $nativePrefix);
        }
        return $propertyR;
    }


    /**
     * Sub parsing method used to factorize parsing (as swagger is not fully regular)
     *
     * @param PropertyRepresentation $propertyR
     * @param $dictionary
     * @param $nativePrefix
     */
    private function _parsePropertyType(PropertyRepresentation $propertyR, $dictionary, $nativePrefix) {
        if (array_key_exists(BJM_ITEMS, $dictionary)) {
            $subDictionary = $dictionary[BJM_ITEMS];
            $propertyR->type = FlexionsTypes::COLLECTION;
            $this->_propertyFromDictionary($propertyR, $subDictionary, $nativePrefix);
        } else {
            $this->_propertyFromDictionary($propertyR, $dictionary, $nativePrefix);
        }
    }

    /**
     * @param $propertyR PropertyRepresentation
     * @param $dictionary
     */
    private function _propertyFromDictionary($propertyR, $dictionary, $nativePrefix) {

        if (array_key_exists(BJM_ENUM, $dictionary)) {
            $propertyR->type = FlexionsTypes::ENUM;
            $enums = $dictionary[BJM_ENUM];
            foreach ($enums as $enumerableElement) {
                $propertyR->enumerations[] = $enumerableElement;
            }
            if (array_key_exists(BJM_ENUM, $dictionary)) {
                $propertyR->emumPreciseType = $dictionary[BJM_ENUM_PRECISE_TYPE];
            } else {
                $propertyR->emumPreciseType = "Enum extended type is not defined for property " . $propertyR->name;
            }
        }

        $swaggerType = null;
        if (array_key_exists(BJM_TYPE, $dictionary)) {
            $swaggerType = $dictionary[BJM_TYPE];
            $propertyR->metadata['BJM_TYPE'] = $swaggerType;
        }

        if (array_key_exists(BJM_INSTANCE_OF, $dictionary)) {
            $propertyR->instanceOf = $dictionary[BJM_INSTANCE_OF];;
        }

        if (array_key_exists(BJM_IS_DYNAMIC, $dictionary)) {
            $propertyR->isDynamic = $dictionary[BJM_IS_DYNAMIC];
        }


        if ($propertyR->type == FlexionsTypes::ENUM) {
            $propertyR->isDynamic = false;
        }

        $swaggerFormat = null;
        if (array_key_exists(BJM_FORMAT, $dictionary)) {
            $swaggerFormat = $dictionary[BJM_FORMAT];
            $propertyR->metadata['BJM_FORMAT'] = $swaggerFormat;
        }

        if (array_key_exists(BJM_REF, $dictionary)) {
            $ref = $dictionary[BJM_REF];
            // Its it a single reference.
            if (!isset($propertyR->type)) {
                $propertyR->type = FlexionsTypes::OBJECT;
            }
            if (!isset($propertyR->instanceOf)) {
                $propertyR->instanceOf = $this->_typeFromRef($ref, $nativePrefix);
            }
            $propertyR->isGeneratedType = true;
        } else {

            if (($propertyR->type == FlexionsTypes::COLLECTION) || $propertyR->type == FlexionsTypes::ENUM) {
                if (!isset($propertyR->instanceOf)) {
                    $propertyR->instanceOf = $this->_jsmTypeToFlexions($swaggerType, $swaggerFormat);
                }
            } else if (($propertyR->type == FlexionsTypes::OBJECT) && (isset($propertyR->instanceOf))) {
                $propertyR->type = $propertyR->instanceOf;
            } else {
                $propertyR->type = $this->_jsmTypeToFlexions($swaggerType, $swaggerFormat);
            }
        }


        if (array_key_exists(BJM_DESCRIPTION, $dictionary)) {
            $propertyR->description = $dictionary[BJM_DESCRIPTION];
        }

        // EXPLICIT TYPE EXTENSION
        if (array_key_exists(BJM_EXPLICIT_TYPE, $dictionary)) {
            $explicitType = $dictionary[BJM_EXPLICIT_TYPE];
            $propertyR->instanceOf = $explicitType;
            if ($propertyR->type === FlexionsTypes::NOT_SUPPORTED) {
                $propertyR->type = FlexionsTypes::OBJECT;
            }
            $propertyR->isGeneratedType = true;// Even if its false
        }

        // DISCREET SERIALIZATION SUPPORT
        if (array_key_exists(BJM_SERIALIZABLE, $dictionary)) {
            $propertyR->isSerializable = $dictionary[BJM_SERIALIZABLE];
        }

        // DISCREET OBSERVABLE SUPPORT
        if (array_key_exists(BJM_SUPERVISABLE, $dictionary)) {
            $propertyR->isSupervisable = $dictionary[BJM_SUPERVISABLE];
        }

        // DISCREET CRYPTABLE SUPPORT
        if (array_key_exists(BJM_CRYPTABLE, $dictionary)) {
            $propertyR->isCryptable = $dictionary[BJM_CRYPTABLE];
        }

        if (array_key_exists(BJM_REQUIRED, $dictionary)) {
            $propertyR->required = $dictionary[BJM_REQUIRED];
        }
        if (array_key_exists(BJM_DEFAULT, $dictionary)) {
            $propertyR->default = $dictionary[BJM_DEFAULT];
        }

        if (array_key_exists(BJS_PROPERTY_METHOD, $dictionary)) {
            $propertyR->method = $dictionary[BJS_PROPERTY_METHOD];
        }

        if (array_key_exists(BJS_PROPERTY_MUTABILITY, $dictionary)) {
            $propertyR->mutability = $dictionary[BJS_PROPERTY_MUTABILITY];
        }

        if (array_key_exists(BJM_PROPERTY_SCOPE, $dictionary)) {
            $propertyR->scope = $dictionary[BJM_PROPERTY_SCOPE];
        }
    }


    private function _typeFromRef($ref, $nativePrefix) {
        $components = explode('/', $ref);
        $instanceOf = end($components);
        $type = $nativePrefix . ucfirst($instanceOf); // We add the prefix
        return $type;
    }


    /**
     * @param $type
     * @param $format
     * @return string
     */
    private function _jsmTypeToFlexions($type, $format) {
        $type = strtolower($type);
        if ($type == 'string') {
            return FlexionsTypes::STRING;
        }
        if ($type == 'integer') {
            return FlexionsTypes::INTEGER;
        }
        if ($type == 'long') {
            return FlexionsTypes::INTEGER;
        }
        if ($type == 'float') {
            return FlexionsTypes::FLOAT;
        }
        if ($type == 'double') {
            return FlexionsTypes::DOUBLE;
        }
        if ($type == 'byte') {
            return FlexionsTypes::BYTE;
        }
        if ($type == 'boolean') {
            return FlexionsTypes::BOOLEAN;
        }
        if ($type == 'file') {
            return FlexionsTypes::FILE;
        }
        // Non standard Swagger
        if ($type == 'url') {
            return FlexionsTypes::URL;
        }

        if ($type == 'date' || $type == 'dateTime') {
            return FlexionsTypes::DATETIME;
        }
        // EXTENSION
        if ($type == 'dictionary') {
            return FlexionsTypes::DICTIONARY;
        }
        if ($type == 'data') {
            return FlexionsTypes::DATA;
        }
        return FlexionsTypes::NOT_SUPPORTED;
    }

    /**
     * @param String $path
     * @return string
     */
    private function _classNameForPath($path) {
        $components = explode('/', $path);
        $className = '';
        foreach ($components as $component) {
            preg_match('#\{(.*?)\}#', $component, $match);

            if (is_null($match) || count($match) == 0) {
                $className .= ucfirst($component);
            } else {
                $cp = $match[1];
                $className .= 'With' . ucfirst($cp);
            }
        }
        return $className;
    }

    /**
     * @param $filePath string
     * @return mixed an associative array from the JSON file
     * @throws Exception
     */
    private function _arrayFromPath($filePath) {
        if (file_exists($filePath)) {
            $string = file_get_contents($filePath);
            return json_decode($string, true);
        } else {
            throw new Exception('Unexisting file ' . $filePath);
        }
    }

}