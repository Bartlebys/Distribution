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

require_once FLEXIONS_ROOT_DIR.'/flexions/utilities/Pluralization.php';

class ActionRepresentation {

    /**
     * @var string Name of the Action
     */
    public $name;

    /**
     * @var string Class of the Action object
     */
    public $class;


    /* @var string the collection name if relevent */
    public $collectionName;

    /**
     * @var array of PropertyRepresentation
     */
    public $parameters = array();

    /**
     * @var array  an associative array  of PropertyRepresentation per status
     * e.g:
     * { "200"=> PropertyRepresentation,"400"=> PropertyRepresentation }
     * or { "success" => PropertyRepresentation, "failure" => PropertyRepresentation }
     * for each errors that could occur when running the Action
     */
    public $responses = array();


    /**
     * The security context for the action.
     * @var SecurityContextRepresentation
     */
    public $security;


    ////////////////////
    // END POINTS ONLY
    ////////////////////

    /**
     * @var string
     */
    public $path;

    /**
     * @var string HTTP method
     */
    public $httpMethod;


    //////////////////
    // DESCRIPTION
    //////////////////


    /**
     * @var string This is a short summary of what the Action does
     */
    public $summary;

    /**
     * @var string A longer text field to explain the behavior of the operation.
     */
    public $notes;

    /**
     * @var string Information about the response returned by the Action
     */
    public $responseNotes;


    //////////////////
    // METADATA
    //////////////////


    /**
     * @var array an associative array to pass specific metadata (including urdMode)
     */
    public $metadata = array();


    /**
     * if set to true Actions could be URD ( Upsert Read Delete)
     * instead of CRUD (Create Read Update Delete)
     * @return boolean
     */
    public function usesUrdMode() {
        return array_key_exists(METADATA_KEY_FOR_USE_URD_MODE, $this->metadata) ? $this->metadata[METADATA_KEY_FOR_USE_URD_MODE] : DEFAULT_USE_URD_MODE;
    }


    //////////////////////////
    // PARAMETERS ITERATORS
    //////////////////////////


    /**
     * Current iteration parameter
     * @var int
     */
    protected $_parameterIndex = -1;


    /**
     * Return true while there is a parameter
     * @return boolean
     */
    public function iterateOnParameters() {
        $this->_parameterIndex++;
        if ($this->_parameterIndex < count($this->parameters)) {
            return true;
        } else {
            // Reinitialise
            $this->_parameterIndex = -1;
            return false;
        }
    }

    /**
     * Returns the current iterated parameter
     * @return PropertyRepresentation
     */
    public function getParameter() {
        $nb = count($this->parameters);
        if ($this->_parameterIndex < $nb && $nb > 0) {
            $keys = array_keys($this->parameters);
            return $this->parameters[$keys[$this->_parameterIndex]];
        }
        return null;
    }

    /**
     *
     * @return boolean
     */
    public function firstParameter() {
        return ($this->_parameterIndex == 0);
    }

    /**
     *
     * @return boolean
     */
    public function lastParameter() {
        return ($this->_parameterIndex == count($this->parameters) - 1);
    }

    //////////////////////////
    // RESPONSE ITERATORS
    //////////////////////////


    /**
     * Current iteration response
     * @var int
     */
    protected $_responseIndex = -1;


    /**
     * Return true while there is a response
     * @return boolean
     */
    public function iterateOnResponses() {
        $this->_responseIndex++;
        if ($this->_responseIndex < count($this->responses)) {
            return true;
        } else {
            // Reinitialise
            $this->_responseIndex = -1;
            return false;
        }
    }

    /**
     * Returns the current iterated response
     * @return PropertyRepresentation
     */
    public function getresponse() {
        $nb = count($this->responses);
        if ($this->_responseIndex < $nb && $nb > 0) {
            $keys = array_keys($this->responses);
            return $this->responses[$keys[$this->_responseIndex]];
        }
        return null;
    }

    /**
     *
     * @return boolean
     */
    public function firstResponse() {
        return ($this->_responseIndex == 0);
    }

    /**
     *
     * @return boolean
     */
    public function lastResponse() {
        return ($this->_responseIndex == count($this->responses) - 1);
    }

    //////////////////////////////////////
    // GENERATIVE FACILITY
    /////////////////////////////////////


    /**
     * Returns true if there are parameters out of the path
     *
     * e.g:$path="/user/{username}"
     * "parameters": [ { "name": "username",...}]
     * Would return false
     * @return bool
     */
    public function containsParametersOutOfPath() {
        preg_match_all('/{(.*?)}/', $this->path, $matches);
        $variablesInPath = $matches[1];
        /* @var $parameter PropertyRepresentation */
        foreach ($this->parameters as $parameter) {
            if (!in_array($parameter->name, $variablesInPath)) {
                return true;
            }
        }
        return false;
    }

    public function parameterIsInPath($parameterName) {
        preg_match_all('/{(.*?)}/', $this->path, $matches);
        $variablesInPath = $matches[1];
        return in_array($parameterName, $variablesInPath);
    }


    /**
     * @return PropertyRepresentation
     */
    public function getSuccessResponse() {
        /*@var $propertyInstance PropertyRepresentation */
        foreach ($this->responses as $key => $propertyInstance) {
            if ($propertyInstance->name == "200" ||
                $propertyInstance->name == "201" ||
                $propertyInstance->name == "202" ||
                $propertyInstance->name == "success"
            ) {
                return $propertyInstance;
            }
        }
        $default = new PropertyRepresentation();
        $default->name = "default";
        $default->type = FlexionsTypes::VOID;
        return $default;
    }


    /**
     * Returns the name of the type that is concerned by the action.
     * @return null|string
     */
    public function concernedType(){
        if (!isset($this->name)){
            if(isset($this->collectionName)){
                $collectionName=$this->collectionName;
                $typeName=ucfirst(Pluralization::singularize($collectionName));
                return $typeName;
            }
        }
        return NULL;
    }
}

?>