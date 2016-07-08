<?php

namespace Bartleby\Core;

require_once BARTLEBY_ROOT_FOLDER . 'Core/IAuthentified.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/EndPoints/Auth.php';

use Bartleby\Core\KeyPath;
use Bartleby\EndPoints\Auth;
use Bartleby\EndPoints\AuthCallData;
use \MongoClient;
use \MongoCollection;
use \MongoCursorException;
use \MongoDB;
use \MongoId;


/*
 * The GateKeeper implements the security layer of Bartleby's framework.
 */

class GateKeeper {

    /* @var Context */
    private $_context;

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
    private $_currentUserUID = NULL;
    
    /**
     * @param Context $configuration
     * @param string $className
     * @param string $methodName
     */
    public function __construct(Context $context, $className, $methodName) {
        $this->_context = $context;
        $components = explode('\\', $className);
        $this->_className = end($components);
        $this->_methodName = $methodName;
    }


    /**
     * @param $parameters
     * @return bool
     */
    public function isAuthorized() {

        if (!isset($this->_context) || !isset($this->_className) || !isset($this->_methodName)) {
            throw new \Exception("Context, Class Name and MethodName  required");
        }

        $key = $this->_className . '->' . $this->_methodName;
        $rules = $this->getConfiguration()->getPermissionsRules();
        if (array_key_exists($key, $rules)) {
            $currentRuleValue = $rules[$key];
            $level = $this->_levelOfRule($currentRuleValue);
            $this->_context->consignIssue('Applicable rule level = ' . $level . '. ',__FILE__,__LINE__);
            if ($level == PERMISSION_IS_BLOCKED) {
                // We block even the SuperAdmins
                return false;
            }
            $allowed = $this->_resultOfRule($level, $key, $currentRuleValue);
            if ($allowed) {
                return true;
            }
        } else {
            $this->_context->consignIssue("Unexisting permission rule key! " . $key,__FILE__,__LINE__);
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

                        case PERMISSION_BY_IDENTIFICATION:
                            return PERMISSION_BY_IDENTIFICATION;

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
                return ($this->_tokenIsValid($rule, $this->_context->getSpaceUID()));

            case PERMISSION_PRESENCE_OF_A_COOKIE:
                return $this->_context->hasUserAuthCookie($this->_context->getSpaceUID());

            case PERMISSION_BY_IDENTIFICATION:
                return $this->_identityIsValid($this->_context->getSpaceUID());

            case PERMISSION_RESTRICTED_TO_ENUMERATED_USERS:

                if (!$this->_resultOfRule(PERMISSION_BY_IDENTIFICATION, $key, $rule)) {
                    return false;
                }

                if (array_key_exists(IDS_KEY, $rule)) {
                    $ids = $rule[IDS_KEY];
                    if (is_array($ids)) {
                        $isEnumerated = in_array($this->_context->getCurrentUserUID(), $ids);
                        return $isEnumerated;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

            case PERMISSION_RESTRICTED_BY_QUERIES:

                if (!$this->_resultOfRule(PERMISSION_BY_IDENTIFICATION, $key, $rule)) {
                    return false;
                }

                if (array_key_exists(ARRAY_OF_QUERIES, $rule)) {
                    $queries = $rule[ARRAY_OF_QUERIES];

                    if (is_array($queries) && count($queries) > 0) {

                        $userID = $this->_context->getCurrentUserUID();
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


                                foreach ($queries as $query) {
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
                                        $variables=$this->_context->getVariables();
                                        $parameterValue = KeyPath::valueForKeyPath($variables, $parameterKeyPath);

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
                                                    if (array_key_exists($resultParameterKey, $variables)) {
                                                        $operand2 = $variables[$resultParameterKey];
                                                    }
                                                }

                                                if (isset($operand1) && isset($operand2)) {

                                                    // PROCEED TO EVALUATION
                                                    $operator = $query[COMPARE_WITH_OPERATOR];
                                                    $result = false;
                                                    $toBeEvaluated = '$result=("' . $operand1 . '"' . $operator . '"' . $operand2 . '");';
                                                    eval($toBeEvaluated);
                                                    if ($result == true) {
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

                if (!$this->_resultOfRule(PERMISSION_BY_IDENTIFICATION, $key, $rule)) {
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
        $currentUID = $this->_context->getCurrentUserUID();
        $ids = $this->getConfiguration()->getSuperAdminUIDS();
        if (is_array($ids)) {
            if (in_array($currentUID, $ids)) {
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
        if ($this->getConfiguration()->BY_PASS_SALTED_TOKENS()) {
            return true;
        } else if (array_key_exists(TOKEN_CONTEXT_KEY, $rule)) {
            $context = $rule[TOKEN_CONTEXT_KEY];
            $tokenHeaderkey = str_replace("#" . SPACE_UID_KEY, "#" . $spaceUID, $context);
            $saltedTokenHeaderkey = $this->getConfiguration()->saltWithSharedKey($tokenHeaderkey);
            $expectedValue = $this->getConfiguration()->saltWithSharedKey($saltedTokenHeaderkey);
            $variables=$this->_context->getVariables();
            if (array_key_exists($saltedTokenHeaderkey, $variables)) {
                $matches = ($variables[$saltedTokenHeaderkey] == $expectedValue);
                $this->_context->consignIssue("Token is exists, " . ($matches ? "and its value matches! " : " but its value is not matching! "),__FILE__,__LINE__);
                return $matches;
            } else {
                $this->_context->consignIssue( 'Unexisting token key! ',__FILE__,__LINE__);
            }
        }

        return false;
    }


    /**
     * Returns true if we have found a valid user for this spaceUID
     * @return bool
     */
    private function _identityIsValid($spaceUID) {
        // The user is extracted via "kvid" httpheader value or via a Cookie.
        $userID = $this->_context->getCurrentUserUID();
        if (isset($userID)) {
            $this->_context->consignIssue( 'User has been extracted',__FILE__,__LINE__);
            $user = $this->_getCurrentUser($userID);
            if (isset($user)) {
                return true;
            } else {
                $this->_context->consignIssue( 'User not found! ',__FILE__,__LINE__);
            }
        } else {
            $this->_context->consignIssue( 'User cannot be extracted from cookie! ',__FILE__,__LINE__);
        }

        return false;
    }


    /**
     * Returns the dynamic permission for a given key
     * @param $key
     * @return array
     */
    private function _getDynamicPermissionForKey($key) {
        // Todo implement a fully dynamic approach if necessary
        // The dynamic features may be deprecated.
        return array();
    }


    /**
     * @return array|null the user or null
     */
    private function _getCurrentUser($userID) {
        if (strlen($userID)<24){
            return NULL;
        }
        if (isset($this->_user)) {
            return $this->_user;
        }
        try {
            $db = $this->getDB();
            /* @var \MongoCollection */
            $collection = $db->users;
            $q = array(MONGO_ID_KEY => $userID);
            $u = $collection->findOne($q);
            if (isset($u)) {
                $this->_user = $u;
            }
        } catch (\Exception $e) {
            // Silent
            $this->_context->consignIssue( 'Mongo Exception '.$e->getMessage(),__FILE__,__LINE__);
            return NULL;
        }
        // Store the user in the context for future usages.
        $this->_context->setCurrentUser($this->_user);
        return $this->_user;
    }


    private function _searchEntity($collectionName, $entityKey, $searchValue) {
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
            $this->_context->consignIssue( 'Mongo Exception '.$e->getMessage(),__FILE__,__LINE__);
        }
        return null;
    }


    /**
     * Returns the current userdID extracted from the calling context (cookie , KVids)
     * @return string
     */
    private function _getCurrentUserUID() {
        if (isset($this->_currentUserUID)) {
            return $this->_currentUserUID;
        }
        $this->_currentUserUID = $this->_context->getUserID($this->_context->getSpaceUID());
        return $this->_currentUserUID;
    }


    /**
     * @return boolean
     */
    private function _isInGroup($ids) {
        $usedID = $this->_context->getCurrentUserUID();
        // TODO implement
        return false;
    }


    /***
     * Returns the current configuration
     * @return Configuration
     */
    public function  getConfiguration(){
        return $this->_context->getConfiguration();
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
                $this->_db = $client->selectDB($this->getConfiguration()->MONGO_DB_NAME());
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

    
}