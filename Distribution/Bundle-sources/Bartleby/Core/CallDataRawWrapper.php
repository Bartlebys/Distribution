<?php

namespace Bartleby\Core;
require_once __DIR__.'/IAuthentified.php';


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
class CallDataRawWrapper implements IAuthentified {

    const dID=SPACE_UID_KEY;

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

    public function getDictionary(){
        return $this->_storage;
    }

    ////////////////////
    // IAuthentified
    ////////////////////

    /* @var array the current User*/
    private $_current_user=NULL;

    /**
     * @return array|null
     */
    public function getCurrentUser() {
        return $this->_current_user;
    }

    /**
     * @param array $current_user
     */
    public function setCurrentUser($current_user) {
        $this->_current_user = $current_user;
    }
}