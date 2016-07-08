<?php

namespace Bartleby\Core;
require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/IAuthentified.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/EndPoints/Auth.php';

use  Bartleby\Core\Configuration;
use  Bartleby\EndPoints\Auth;
use Bartleby\Core\IAuthentified;

/**
 * Class Context
 * @package Bartleby\Core
 */
class Context implements IAuthentified{

    /* @var string the controller class name*/
    public $controllerClassName='';
    /* @var string the 'model' class name*/
    public $modelClassName='';
    /*@var string  the method to be called on the controller class*/
    public $method;

    ////////////////////////////
    // Contextual Informations
    // that are serialized when the context is returned for debug purposes
    // For more detailled infos you can call the Infos Endpoint
    ////////////////////////////
    public $infos=array();

    /* @var array An array of issues used for analysis */
    public $issues=array();

    /* @var Configuration */
    private $_configuration;
    /* @var array */
    private $_allVariables=array();
    /* @var boolean */
    private $_hasBeenCleaned=false;


    // The current user may be stored in the context.


    /**
     * Context constructor.
     * @param array $variables
     * @param \Bartleby\Core\Configuration $configuration
     */
    public function __construct(array $variables, Configuration $configuration) {
        $this->_allVariables=$variables;
        $this->_configuration=$configuration;
        $this->infos['runMode']=$configuration->runMode;
        $this->infos[SPACE_UID_KEY]=$this->getSpaceUID();
        $this->infos['cookies']=$_COOKIE;
    }

    /**
     * Add some parameters (e.g url named parameters.
     * @param array $parameters
     */
    public function addParameters(array $parameters){
        $filtered=$this->_cleanInputs($parameters);
        $this->_allVariables=array_merge($this->_allVariables,$filtered);
    }


    ////////////////////
    // IAuthentified
    ////////////////////


    // The curent user is not always available
    // It may be populated by the GateKeeper
    // Or a by Controller.

    /* @var array user as an array */
    private $_currentUser=null;

    /**
     * @return array
     */
    public function getCurrentUser() {
        return $this->_currentUser;
    }

    /**
     * @param array $currentUser
     */
    public function setCurrentUser($currentUser) {
        $this->_currentUser = $currentUser;
    }


    ////////////////////
    ///  ACCESSORS
    ////////////////////

    /**
     * @return \Bartleby\Core\Configuration
     */
    public function getConfiguration(){
        return $this->_configuration;
    }

    /**
     * Returns all the aggregated variables
     * HTTP headers, QueryString, Post parameters
     * and even named url parameters. e.g "/user/{userId}/comments/{numberOfComment}"
     *
     * @return array
     */
    public function getVariables(){
        if ($this->_hasBeenCleaned){
            return $this->_allVariables;
        }
        $this->_allVariables=$this->_cleanInputs($this->_allVariables);
        $this->_hasBeenCleaned=true;
        return $this->_allVariables;
    }


    /**
     * Grabs the SpaceUID
     * @return null|string the dataSpace UID.
     */
    public function getSpaceUID(){
        $spaceUID=NULL;
        if (array_key_exists(SPACE_UID_KEY,$this->_allVariables)){
            $spaceUID=$this->_allVariables[SPACE_UID_KEY];
        }
        // We may have a contextual cookie
        if (!isset($spaceUID) && array_key_exists(CURRENT_SPACE_UID_COOKIE_KEY,$this->_allVariables)){
            $spaceUID=$this->_allVariables[CURRENT_SPACE_UID_COOKIE_KEY];
        }
        return $spaceUID;
    }



    /**
     * Grabs the runUID
     * @return null|string the run UID.
     */
    public function getRunUID(){
        $runUID=NULL;
        if (array_key_exists(RUN_UID_KEY,$this->_allVariables)){
            $runUID=$this->_allVariables[RUN_UID_KEY];
        }
        return $runUID;
    }

    /**
     * Grabs the observationUID
     * @return null|string the Observation UID.
     */
    public function getObservationUID(){
        $observationUID=NULL;
        if (array_key_exists(OBSERVATION_UID_KEY,$this->_allVariables)){
            $observationUID=$this->_allVariables[OBSERVATION_UID_KEY];
        }
        return $observationUID;
    }

    /**
     * @return bool
     */
    public function usePrettyPrint(){
         if (array_key_exists(JSON_PRETTY_PRINT_KEY,$this->_allVariables)){
             $prettyPrint=strtolower($this->_allVariables[JSON_PRETTY_PRINT_KEY]);
             return ($prettyPrint=="true");
         }
        return false;
    }

    /**
     * Returns the current User UID
     * @return null|string
     */
    public function getCurrentUserUID(){
        return $this->getUserID($this->getSpaceUID());
    }


    /**
     * Returns the userID if there is one in the "kvid" HTTP header field or a cookie.
     * @param $spaceUID
     * @return null|string
     */
    public function getUserID($spaceUID){
        if (!isset($spaceUID)){
            $this->consignIssue('spaceUID is not set',__FILE__,__LINE__);
            return NULL;
        }
        // We the "kvid" http header.
        $userUID=$this->_getUserIDFromKVI($spaceUID);
        if (isset($userUID)){
            return $userUID;
        }
        // And fall back on the cookie.
        return $this->_getUserIDFromCookie($spaceUID);
    }


    /**
     * Returns the userID from the kvi
     * @param $spaceUID
     * @return null|string
     */
    private function _getUserIDFromKVI($spaceUID) {
        $allHeader=getallheaders();
        if (array_key_exists(Auth::kvidKey,$allHeader)) {
            $cryptedUserID = $allHeader[Auth::kvidKey];
            $userID = $this->_configuration->decryptIdentificationValue($spaceUID, $cryptedUserID);
            return $userID;
        }
        return NULL;
    }


    /**
     * Returns the userID from the cookie
     * @param $spaceUID
     * @return null|string
     */
    private function _getUserIDFromCookie($spaceUID) {
        if (!isset($_COOKIE)){
            $this->consignIssue('Php\'s _COOKIE global is not existing.',__FILE__,__LINE__);
        }
        $cookieKey = $this->_configuration->getCryptedKEYForSpaceUID($spaceUID);
        if (array_key_exists($cookieKey, $_COOKIE)) {
            $cookieValue = $_COOKIE[$cookieKey];
            $userID = $this->_configuration->decryptIdentificationValue($spaceUID, $cookieValue);
            return $userID;
        }else {
            $this->consignIssue('Cookie key ' . $cookieKey . ' is not existing.',__FILE__,__LINE__);
        }
        return NULL;
    }
    

    /**
     * return true if there is a consistant cookie for the context
     * @param $spaceUID
     * @return bool
     */
    function hasUserAuthCookie($spaceUID) {
        $cookieKey = $this->_configuration->getCryptedKEYForSpaceUID($spaceUID);
        return (array_key_exists($cookieKey, $_COOKIE));
    }


    /**
     * @param $issue string
     * @param $file string
     * @param $line int
     */
    function consignIssue($issue,$file,$line){
        $this->issues[basename($file).'('.$line.')']=$issue;
    }

    /**
     * returns true if an issue with that text has been found
     * @param $text
     * @return bool
     */
    function containsIssueWithText($text){
        foreach ($this->issues as $k=>$issueText) {
            if (strtolower(trim($issueText))==strtolower(trim($text))) {
                return true;
            }
        }
        return false;
    }


    ////////////////////
    /// PRIVATE METHODS
    ////////////////////

    /**
     * Cleans up the inputs
     *
     * @param $data
     * @return array|string
     */
    private function _cleanInputs($data) {
        $clean_input = Array ();
        if (is_array ( $data )) {
            foreach ( $data as $k => $v ) {
                $clean_input [$k] = $this->_cleanInputs ( $v );
            }
        } else {
            $clean_input = trim ( strip_tags ( $data ) );
        }
        return $clean_input;
    }

}