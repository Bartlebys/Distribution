<?php

namespace Bartleby\Core;

require_once __DIR__ . '/IAuthentified.php';
require_once __DIR__ . '/KeyPath.php';
require_once __DIR__ . '/Configuration.php';

use Bartleby\Core\KeyPath;

//TODO Split and create MongoGateKeeper
use \MongoClient;
use \MongoCollection;
use \MongoCursorException;
use \MongoDB;
use \MongoId;


/*
 * The GateKeeper implements the security layer of Bartleby's framework.
 */

class GateKeeper implements IAuthentified{


    /* @var  \Bartleby\Configuration */
    private $_configuration;

    /* @var string */
    private $_className;

    /* @var string */
    private $_methodName;

    /* @var array */
    private $_entitiesUIDS;

    /* @var array */
    private $_user;

    /*@var string */
    private $_currentUserToken;

    /*@var string */
    private $_currentUsedSpaceUID;

    private $_spaceUID = DEFAULT_SPACE_UID;

    // an Array with All the headers merged with the parameters.
    private $_all;

    /* @var string */
    public $explanation="";

    /**
     * @param Configuration $configuration
     * @param string $className
     * @param string $methodName
     */
    public function __construct(Configuration $configuration, $className, $methodName) {
        $this->_configuration = $configuration;
        $components = explode('\\',$className);
        $this->_className = end($components);
        $this->_methodName = $methodName;
        $this->_all = getallheaders();
    }


    /**
     * @param $parameters
     * @return bool
     */
    public function isAuthorized($parameters) {

        if (!isset($this->_configuration) || !isset($this->_className) || !isset($this->_methodName)) {
            throw new \Exception("Configuration, Class Name and MethodName  required");
        }

        if (is_array($parameters)) {
            $this->_all = array_merge($this->_all, $parameters);
            if (array_key_exists(SPACE_UID_KEY, $parameters)) {
                //Use the parameter
                $this->_spaceUID = $parameters[SPACE_UID_KEY];
            } else if (is_array($this->_all)) {
                // Lets try the headers
                if (array_key_exists(SPACE_UID_KEY, $this->_all) == true) {
                    $this->_spaceUID = $this->_all[SPACE_UID_KEY];
                }
            } else {
                // We donnot have any dID
                // _spaceUID will be equal to DEFAULT_spaceUID == 0
            }
        }

        $key = $this->_className . '->' . $this->_methodName;
        $rules = $this->_configuration->getPermissionsRules();
        if (array_key_exists($key, $rules)) {
            $currentRuleValue = $rules[$key];
            $level = $this->_levelOfRule($currentRuleValue);
            $this->explanation.='Applicable rule level = '.$level.'. ';
            if ($level == PERMISSION_IS_BLOCKED) {
                // We block even the SuperAdmins
                return false;
            }
            $allowed = $this->_resultOfRule($level, $key, $currentRuleValue);
            if ($allowed) {
                return true;
            }
        }else{
            $this->explanation.="Unexisting permission rule key! ".$key;
        }

        // By default if the permission is not blocked
        // any super admin is allowed
        return $this->_isSuperAdmin();
    }


    /**
     * Returns the level of the rule very strictly
     * Blocks  dynamic vicious circles and un consistent permissions
     * @param $rule
     * @param bool|false $wasDynamic
     * @return int
     */
    private function _levelOfRule($rule, $wasDynamic = false) {
        if (is_array($rule)) {
            $hasValidIdsList = false;
            if (array_key_exists(IDS_KEY, $rule)) {
                $ids = $rule[IDS_KEY];
                if (is_array($ids)) {
                    if (count($ids) > 0) {
                        $hasValidIdsList = true;
                    }
                }
            }
            if (array_key_exists(LEVEL_KEY, $rule)) {
                $level = $rule[LEVEL_KEY];
                if (is_numeric($level)) {
                    $level = $level + 0;
                    switch ($level) {

                        case PERMISSION_NO_RESTRICTION:
                            return PERMISSION_NO_RESTRICTION;

                        case PERMISSION_BY_TOKEN:
                            return PERMISSION_BY_TOKEN;

                        case PERMISSION_PRESENCE_OF_A_COOKIE:
                            return PERMISSION_PRESENCE_OF_A_COOKIE;

                        case PERMISSION_IDENTIFIED_BY_COOKIE:
                            return PERMISSION_IDENTIFIED_BY_COOKIE;

                        case PERMISSION_RESTRICTED_TO_ENUMERATED_USERS:
                            if ($hasValidIdsList == true) {
                                return PERMISSION_RESTRICTED_TO_ENUMERATED_USERS;
                            } else {
                                return PERMISSION_IS_BLOCKED;
                            }

                        case PERMISSION_RESTRICTED_BY_QUERIES:
                            return PERMISSION_RESTRICTED_BY_QUERIES;

                        case PERMISSION_RESTRICTED_TO_GROUP_MEMBERS:
                            return PERMISSION_RESTRICTED_TO_GROUP_MEMBERS;

                        case PERMISSION_IS_DYNAMIC:
                            if ($wasDynamic == true) {
                                // We do not want a dynamic vicious circle
                                return PERMISSION_IS_BLOCKED;
                            } else {
                                return PERMISSION_IS_DYNAMIC;
                            }

                        case PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY:
                            return PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY;
                    }
                }
            }
        }
        return PERMISSION_IS_BLOCKED;
    }

    /**
     * @param $level
     * @param $key
     * @param $rule
     * @return bool
     */
    private function _resultOfRule($level, $key, $rule) {
        switch ($level) {
            case PERMISSION_NO_RESTRICTION:
                return true;
            case PERMISSION_BY_TOKEN:
                return ($this->_tokenIsValid($rule, $this->_spaceUID));

            case PERMISSION_PRESENCE_OF_A_COOKIE:
                return $this->_configuration->hasUserAuthCookie($this->_spaceUID);

            case PERMISSION_IDENTIFIED_BY_COOKIE:
                return $this->_cookieIsValid($this->_spaceUID);

            case PERMISSION_RESTRICTED_TO_ENUMERATED_USERS:

                if (!$this->_resultOfRule(PERMISSION_IDENTIFIED_BY_COOKIE,$key,$rule)){
                    return false;
                }

                if (array_key_exists(IDS_KEY, $rule)) {
                    $ids = $rule[IDS_KEY];
                    if (is_array($ids)) {
                        $isEnumerated = in_array($this->_getCurrentUsedID(), $ids);
                        return $isEnumerated;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            case PERMISSION_RESTRICTED_BY_QUERIES:

                if (!$this->_resultOfRule(PERMISSION_IDENTIFIED_BY_COOKIE,$key,$rule)){
                    return false;
                }

                if (array_key_exists(ARRAY_OF_QUERIES, $rule)) {
                    $queries=$rule[ARRAY_OF_QUERIES];

                    if( is_array($queries) && count($queries)>0){

                        $userID = $this->_getCurrentUsedID();
                        if (isset($userID)) {
                            $this->_getCurrentUser($userID);
                            if (isset($this->_user)) {
                                /*

                                Syntax of a query

                                       SELECT_COLLECTION_NAME
                                       WHERE_VALUE_OF_ENTITY_KEY
                                       EQUALS_VALUE_OF_PARAMETERS_KEY_PATH

                                       COMPARE_WITH_OPERATOR
                                       RESULT_ENTITY_KEY
                                       AND_PARAMETER_KEY or AND_CURRENT_USERID

                                The result is evaluated

                                */


                                foreach ($queries as $query){
                                    if (

                                        array_key_exists(SELECT_COLLECTION_NAME, $query) &&
                                        array_key_exists(WHERE_VALUE_OF_ENTITY_KEY, $query) &&
                                        array_key_exists(EQUALS_VALUE_OF_PARAMETERS_KEY_PATH, $query) &&
                                        array_key_exists(RESULT_ENTITY_KEY, $query) &&
                                        array_key_exists(COMPARE_WITH_OPERATOR, $query) &&
                                        (array_key_exists(AND_PARAMETER_KEY, $query) || array_key_exists(AND_CURRENT_USERID, $query))
                                    ) {

                                        $collectionName = $query[SELECT_COLLECTION_NAME];
                                        $entityKey = $query[WHERE_VALUE_OF_ENTITY_KEY];
                                        $parameterKeyPath = $query[EQUALS_VALUE_OF_PARAMETERS_KEY_PATH];

                                        $parameterValue = KeyPath::valueForKeyPath($this->_all, $parameterKeyPath);

                                        if (isset($parameterValue)) {


                                            // Fetch the entity
                                            $entity = $this->_searchEntity($collectionName, $entityKey, $parameterValue);

                                            if (isset($entity)) {

                                                $resultEntityKey = $query[RESULT_ENTITY_KEY];
                                                $operand1 = NULL;
                                                $operand2 = NULL;

                                                if (array_key_exists($resultEntityKey, $entity)) {
                                                    $operand1 = $entity[$resultEntityKey];
                                                }

                                                if (array_key_exists(AND_CURRENT_USERID, $query)) {
                                                    $operand2 = $userID;
                                                } else if (!array_key_exists(AND_PARAMETER_KEY, $query)) {
                                                    $resultParameterKey = $query[AND_PARAMETER_KEY];
                                                    if (array_key_exists($resultParameterKey, $this->_all)) {
                                                        $operand2 = $this->_all[$resultParameterKey];
                                                    }
                                                }

                                                if (isset($operand1) && isset($operand2)) {

                                                    // PROCEED TO EVALUATION
                                                    $operator = $query[COMPARE_WITH_OPERATOR];
                                                    $result = false;
                                                    $toBeEvaluated = '$result=("' . $operand1 . '"' . $operator . '"' . $operand2 . '");';
                                                    eval($toBeEvaluated);
                                                    if($result==true){
                                                        return $result;
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }


                            }
                        }
                    }
                }
                return false;


            case PERMISSION_RESTRICTED_TO_GROUP_MEMBERS:

                if (!$this->_resultOfRule(PERMISSION_IDENTIFIED_BY_COOKIE,$key,$rule)){
                    return false;
                }

                if (array_key_exists(IDS_KEY, $rule)) {
                    $ids = $rule[IDS_KEY];
                    if (is_array($ids)) {
                        return $this->_isInGroup($ids);
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            case PERMISSION_IS_DYNAMIC;

                $rule = $this->_getDynamicPermissionForKey($key);
                $wasDynamic = true;
                $level = $this->_levelOfRule($rule, $wasDynamic);
                return $this->_resultOfRule($level, $key, $rule);

            case PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY:
                return $this->_isSuperAdmin();

            case PERMISSION_IS_BLOCKED:
                return false;

        }
        return false;
    }

    /**
     * Returns if the current user is super admin
     * @return bool
     */
    private function _isSuperAdmin() {
        $currentUID = $this->_getCurrentUsedID();
        $ids = $this->_configuration->getSuperAdminUIDS();
        if (is_array($ids)) {
            if (in_array($currentUID, $ids) && $this->_cookieIsValid($this->_spaceUID)) {
                return true;
            }
        }
        return false;
    }


    /// TOKEN

    /**
     * The token validity guarantees that the client knows the shared key
     * and is aware of Bartleby's security token policy.
     * It Verifies the conformity of the Headers or Params with the context,
     * Extracts a Token and compare the expected {key,value}
     * @return bool
     */
    private function _tokenIsValid($rule, $spaceUID) {
        if ($this->_configuration->BY_PASS_SALTED_TOKENS()) {
            return true;
        } else if (array_key_exists(TOKEN_CONTEXT_KEY, $rule)){
            $context = $rule[TOKEN_CONTEXT_KEY];
            $tokenHeaderkey = str_replace("#" . SPACE_UID_KEY, "#" . $spaceUID, $context);
            $saltedTokenHeaderkey = $this->_configuration->saltWithSharedKey($tokenHeaderkey);
            $expectedValue = $this->_configuration->saltWithSharedKey($saltedTokenHeaderkey);
            if (array_key_exists($saltedTokenHeaderkey, $this->_all)) {
                $matches = ($this->_all[$saltedTokenHeaderkey] == $expectedValue);
                $this->explanation.="Token is exists, ".( $matches ? "and its value matches! ":" but its value is not matching! ");
                return $matches;
            }else{
                $this->explanation.='Unexisting token key! ';
            }
        }

        return false;
    }


    /**
     * The cookies are crypted.
     * Returns true if the crypted user id exists.
     * @return bool
     */
    private function _cookieIsValid($rRUID) {
        $userID = $this->_configuration->getUserIDFromCookie($rRUID);
        $this->explanation.='Configuration.issues:('.implode(' ',$this->_configuration->issues).') ';
        if (isset($userID)) {
            $this->explanation.="User has been extracted from cookie! ";
            $user = $this->_getCurrentUser($userID);
            if (isset($user)) {
                return true;
            }else{
                $this->explanation.="User not found! ";
            }
        }else{
            $this->explanation.="User cannot be extracted from cookie! ";
        }
        return false;
    }

    /**
     * Returns the dynamic permission for a given key
     * @param $key
     * @return array
     */
    private function  _getDynamicPermissionForKey($key) {
        // Todo implement a fully dynamic approach if necessary
        // The dynamic features may be deprecated.
        return array ();
    }


    /**
     * @return array
     */
    private function _getCurrentUser($userID) {
        if (isset($this->_user)) {
            return $this->_user;
        }
        $db = $this->getDB();
        /* @var \MongoCollection */
        $collection = $db->users;
        $q = array(MONGO_ID_KEY => $userID);
        try {
            $u = $collection->findOne($q);
            if (isset($u)) {
                $this->_user = $u;
            }
        } catch (\Exception $e) {
            // Silent
        }
        return $this->_user;
    }


    private function _searchEntity($collectionName,$entityKey,$searchValue) {
        try {
            $db = $this->getDB();
            /* @var \MongoCollection */
            $collection = $db->{$collectionName};
            $q = array($entityKey => $searchValue);
            $r = $collection->findOne($q);
            if (isset($r)) {
                return $r;
            }
        } catch (\Exception $e) {
            // Silent
        }
        return null;
    }


    /**
     * Returns the current usedID form the cookie
     * @return string
     */
    private function _getCurrentUsedID() {
        if (isset($this->_currentUsedSpaceUID)) {
            return $this->_currentUsedSpaceUID;
        }
        $this->_currentUsedSpaceUID = $this->_configuration->getUserIDFromCookie($this->_spaceUID);
        return $this->_currentUsedSpaceUID;
    }


    /**
     * @return boolean
     */
    private function _isInGroup($ids) {
        $usedID = $this->_getCurrentUsedID();
        // TODO implement
        return false;
    }


    //////////////
    /// MONGO
    //////////////

    /**
     * The MongoDB
     *
     * @var $_db MongoDB
     */
    private $_db;// COUPLAGE A REDUIRE PLUS TARD


    protected function getDB() {
        if (!isset($this->_db)) {
            $client = $this->getMongoClient();
            if (isset($client)) {
                $this->_db = $client->selectDB($this->_configuration->MONGO_DB_NAME());
            }
        }
        return $this->_db;
    }

    /**
     * @return MongoClient
     */
    protected function getMongoClient() {
        if (!isset($this->_mongoClient)) {
            $this->_mongoClient = new MongoClient ();
        }
        return $this->_mongoClient;
    }

    
    ////////////////////
    // IAuthentified
    ////////////////////

    /*
     * @return array|null
     */
    public function getCurrentUser() {
        return $this->_user;
    }

    /**
     * @param array $current_user
     */
    public function setCurrentUser($current_user) {
        // For security purposes we prefer not to alter the $current_user
        /// $this->_user = $current_user;
    }


}