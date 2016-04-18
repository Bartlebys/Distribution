<?php

namespace Bartleby\Core;

interface IPersistentController{

    public function getUser();

    public function authenticationIsValid ();

}

/**
 * Class Controller
 * @package Bartleby\Core
 */
class Controller {

    /**
     * @var Configuration
     */
    protected $_configuration;

    /**
     * @var string
     */
    protected $_userID;


    /**
     * Constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration) {
        $this->_configuration = $configuration;
    }

    /**
     *
     * @return bool
     */
    public function isAuthenticated($spaceUID){
        $this->_userID=$this->getCurrentUserID($spaceUID);
        return isset($this->_userID)?true:false;
    }


    /**
     * Returns the current user ID for a given related UID dID
     * @return null|string
     */

    /**
     * Return the current user id for the dID
     * @param $spaceUID
     * @return null|string
     */
    public function getCurrentUserID($spaceUID){
        if(isset($this->_userID)){
            return $this->_userID;
        }
       $this->_userID=$this->_configuration->getUserIDFromCookie($spaceUID);
        return $this->_userID;
    }

}