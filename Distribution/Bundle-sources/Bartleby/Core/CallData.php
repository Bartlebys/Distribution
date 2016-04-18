<?php

namespace Bartleby\Core;

require_once __DIR__.'/IAuthentified.php';
require_once __DIR__.'/Model.php';



/**
 * Class CallData
 * You can use CallData to build strongly typed calls
 *
 * And you can use a CallDataRawWrapper
 * when #the code is generated#
 * you can bypass serialization/deserialization
 *
 * @package Bartleby\Core
 */
class CallData extends Model implements IAuthentified{
    
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

