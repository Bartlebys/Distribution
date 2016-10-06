<?php

namespace Bartleby\Core;

/**
 * Class CallDataRawWrapper
 * You can use CallData to build strongly typed calls
 *
 * And you can use a CallDataRawWrapper
 * when the code is generated or intensively tested
 * it will bypass serialization/deserialization
 *
 * @package Bartleby\Core
 */
class CallDataRawWrapper {

    private $_storage;

    private $_isArray=true;
    
    /**
     * CallData constructor.
     * @param array $data
     */
    public function __construct($data) {
        $this->_isArray=is_array($data);
        $this->_storage = $data;
    }

    public function getValueForKey($key){
        if($this->_isArray){
            if(array_key_exists($key,$this->_storage)){
                return $this->_storage[$key];
            }
        }else{
            return $this->_storage->{$key};
        }
        return null;
    }


    public function keyExists($key){
        if($this->_isArray) {
            return array_key_exists($key, $this->_storage);
        }else{
            return property_exists($this->_storage,$key);
        }

    }


    public function getDictionary(){
        return $this->_storage;
    }

    //////////////////////////
    // Facilities
    //////////////////////////

    // Sort values must be casted to numeric values
    // So we propose some facilities to transform

    function getCastedDictionaryForKey($key){
        return $this->castNumericValues($this->getValueForKey($key));
    }

    function castNumericValues($values) {
        if (!is_array($values)) {
            return $values;
        }
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $this->castNumericValues($value);
            } else {
                if (is_numeric($value)) {
                    $values[$key] = 0 + $value;
                }
            }
        }
        return $values;
    }

}