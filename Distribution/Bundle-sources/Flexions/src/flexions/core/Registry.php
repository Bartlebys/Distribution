<?php

require_once __DIR__ . '/BJMParser.php';

if (!defined('REGISTRY_CLASS_NAME')) {
    define('REGISTRY_CLASS_NAME', 'className');
    define('REGISTRY_CLASS_IMPORTS', 'imports');
    define('REGISTRY_CLASS_PROPERTIES', 'properties');
}

/**
 * Class Registry instantiate dynamically instances and variables
 * It can store JSON serialized primitive and instanciate compliants classes.
 * Check for example : Bartleby.Commons.flexions/descriptors/project.json["templates.project.variables"]
 */
class Registry {

    /* @var Registry */
    private static $_instance;

    /**
     * Call this method to get the current singleton
     * @return Registry
     */
    public static function Instance() {
        if (self::$_instance === NULL) {
            self::$_instance = new Registry ();
        }
        return self::$_instance;
    }

    /***
     * Reinitializes the variables
     */
    public function reset(){
        $this->_variables=array();
    }


    private $_variables=array();

    /**
     * @return array
     */
    public function getVariables() {
        return $this->_variables;
    }

    /**
     * Returns the variable value for a given key
     * @param string $variableName
     * @param bool $resolve the value
     * @return mixed|null
     */
    public function valueForKey($variableName,$resolve=true){
        if(array_key_exists($variableName,$this->_variables)) {
            $value=$this->_variables[$variableName];
            if ($resolve && is_string($value)) {
                return BJMParser::resolvePath($value);
            } else {
                return $value;
            }
        }
        return NULL;
    }


    /**
     * Returns if a variable is defined
     * @param $variableName
     * @return bool
     */
    public function defined($variableName){
        return array_key_exists($variableName,$this->_variables);
    }


    /**
     * Adds and instantiate dynamically instances and variables from a Dictionary
     * @param array $dictionary
     * @throws Exception
     */
    public function defineVariables(array $dictionary) {

        foreach ($dictionary as $key => $value) {
            // $key define the global var name
            if (is_array($value) && array_key_exists(REGISTRY_CLASS_NAME, $value)) {

                ////////////
                // Classes.
                ////////////

                // 1. Grab the Class name.
                $className = $value[REGISTRY_CLASS_NAME];

                // Imports
                // We can import one or multiple imports depending on the type.
                if (array_key_exists(REGISTRY_CLASS_IMPORTS, $value)) {
                    $imports = $value[REGISTRY_CLASS_IMPORTS];
                    if (is_array($imports)) {
                        foreach ($imports as $import) {
                            $import = BJMParser::resolvePath($import);
                            require_once $import;
                        }
                    } else if (is_string($imports)) {
                        $import = BJMParser::resolvePath($imports);
                        require_once $import;
                    } else {
                        throw new Exception('Illegal ' . REGISTRY_CLASS_IMPORTS . ' type ');
                    }
                }

                // 2. Instantiate the class
                $this->_variables[$key] = new $className();

                // 3. Populate its properties.
                if (array_key_exists(REGISTRY_CLASS_PROPERTIES, $value)) {
                    $properties = $value[REGISTRY_CLASS_PROPERTIES];
                    if (is_array($properties)) {
                        foreach ($properties as $propertyKey => $propertyValue) {
                            // Set the value of the property
                            $this->_variables[$key]->{$propertyKey} = $propertyValue;
                        }
                    } else {
                        throw new Exception(REGISTRY_CLASS_PROPERTIES . ' must be defined in an array');
                    }
                }

            } else {

                ////////////
                // Primitives
                ////////////

                $this->_variables[$key] = $value;
            }
        }
    }
}