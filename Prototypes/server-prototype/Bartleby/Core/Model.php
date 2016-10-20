<?php

namespace Bartleby\Core;

require_once __DIR__.'/ModelTransformer.php';


/**
 * Interface IPatchableFromDictionary
 * @package Bartleby\Core
 */
interface IPatchableFromDictionary {
    /**
     * When a member is a Model
     * you should add its class name to the mapping
     * Don't forget to import and declare the usage of the class.
     *
     * function classMapping(array $mapping=array()){
     *   $mapping['asset']='Asset';
     *   return parent::classMapping($mapping);
     * }
     *
     * How to map a collection ?
     * That's very easy :
     *
     * function classMapping(array $mapping=array()){
     *   $mapping['assets']=array('Asset');
     *   return parent::classMapping($mapping);
     * }
     *
     * @param array $mapping
     * @return array
     */
    function classMapping(array $mapping = array());
}


class Model implements IPatchableFromDictionary {

    public $UID;

    private $_classMapping;

    /**
     * A Model is a typed object that
     * can be populated from a an associative Array
     *
     * @param array $dictionary
     * @throws \Exception
     */
    public function __construct(array $dictionary=array()) {
        if(is_array($dictionary)){
            $this->patchFromDictionary($dictionary);
        }
    }


    /**
     * A generic method to patch an object with a bunch of key Values
     * @param array $dictionary
     * @return mixed
     */
    public final function patchFromDictionary(array $dictionary) {
        if (!empty($dictionary)) {
            foreach ($dictionary as $key => $value) {
                $this->patchKeyWithValue($key, $value);
            }
        }
    }

    /**
     * Apply the patch for a given key.
     * @param $key
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public final function patchKeyWithValue($key, $value) {
        $mapping = $this->classMapping();
        if (array_key_exists($key, $mapping)) {
            if (is_array($value) || !isset($value)) {
                $class = $mapping[$key];
                if(is_array($class)){
                    // It is a collection
                    $class=$class[0];
                    $this->{$key}=array();
                    foreach ($value as $itemValue) {
                        // Add the sub model to the collection
                        $this->{$key}[]=$this->_getSubModel($key,$class,$itemValue);
                    }
                }else{
                   $this->{$key}=$this->_getSubModel($key,$class,$value);
                }
            } else {
                throw new \Exception($key . ' must be populated from a dictionary');
            }
        } else {
            $this->{$key} = $value;
        }
    }


    /**
     *
     * Return the submodel
     * @param $key
     * @param $value
     * @param $classnameOrTransformerInstance
     * @return mixed
     * @throws \Exception
     */
    private function _getSubModel($key,$value,$classnameOrTransformerInstance){
        if(is_string($classnameOrTransformerInstance)){
            $submodel = new $classnameOrTransformerInstance();
            if (method_exists($submodel, 'patchFromDictionary')) {
                if (isset($value)) {
                    $submodel->patchFromDictionary($value);
                    return $submodel;
                }
            } else {
                throw new \Exception('Submodel ' . $key . ' should implement patchFromDictionary');
            }
        }else{
            // It is a transformer it will map the dictionary to a model.
            if(  is_subclass_of($classnameOrTransformerInstance,'Transformer')){
                return $classnameOrTransformerInstance->modelFromDictionary($value);
            }else{
                throw new \Exception( $classnameOrTransformerInstance . 'should be a model transformer');
            }

        }
        return null;

    }

    /**
     * When a member is a Model
     * you should add its class name to the mapping
     * Don't forget to import and declare the usage of the class.
     *
     * public function classMapping(array $mapping=array()){
     *
     *      return parent::_classMapping(array('mySubModel'=>"MySubModelClassName"));
     * }
     *
     *
     * @param array $mapping
     * @return array
     */
    function classMapping(array $mapping = array()) {
        if (!isset($this->_classMapping)) {
            $this->_classMapping = $mapping;
        }
        return $this->_classMapping;
    }


    // Todo implement toDictionary() - generative ?




}