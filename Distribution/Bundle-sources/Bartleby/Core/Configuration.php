<?php


namespace Bartleby\Core;

require_once __DIR__ . '/Stages.php';

if (!defined('PERMISSION_IS_STATIC')) {

    define('PERMISSION_IS_STATIC', 0);

    // PERMISSION_RESTRICTED_TO_ENUMERATED_USERS is equivalent
    // to PERMISSION_IS_STATIC+PERMISSION_RESTRICTED_TO_ENUMERATED_USERS

    define('PERMISSION_NO_RESTRICTION', 1);
    define('PERMISSION_BY_TOKEN', 2);
    define('PERMISSION_PRESENCE_OF_A_COOKIE', 3);
    define('PERMISSION_IDENTIFIED_BY_COOKIE', 4);
    define('PERMISSION_RESTRICTED_TO_ENUMERATED_USERS', 5);
    define('PERMISSION_RESTRICTED_BY_QUERIES', 6);
    define('PERMISSION_RESTRICTED_TO_GROUP_MEMBERS', 7);

    // Explicit you need to PERMISSION_IS_DYNAMIC
    define('PERMISSION_IS_DYNAMIC', 32768);// We reserve the ability to add new permission types

    define('PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY', 65536);
    define('PERMISSION_IS_BLOCKED', 131072);// For security purpose we can decide to lock the super admin

    define('TOKEN_CONTEXT', 'context');

    define('UID_KEY', 'UID');
    define('MONGO_ID_KEY', '_id'); // For example : PERMISSION_RESTRICTED_TO_ENUMERATED_USERS the ids of the users
    define('SPACE_UID_KEY', 'spaceUID');
    define('OBSERVABLE_UID_KEY', 'observableUID');
    define('LEVEL_KEY', 'level'); // The permission level.
    define('KEY_NAME', 'name'); // For example : when PERMISSION_PRESENCE_OF_A_TOKEN the name == the token key
    define('IDS_KEY', 'ids'); // For example : PERMISSION_RESTRICTED_TO_ENUMERATED_USERS the ids of the users

    // SYNTAX FOR PERMISSION_RESTRICTED_BY_QUERIES
    define('ARRAY_OF_QUERIES', 'queries');
    define('SELECT_COLLECTION_NAME', 'collectionName');
    define('WHERE_VALUE_OF_ENTITY_KEY', 'entitySelectionKey');
    define('EQUALS_VALUE_OF_PARAMETERS_KEY_PATH', 'paramKeyPath');
    define('COMPARE_WITH_OPERATOR', 'operator');
    define('RESULT_ENTITY_KEY', 'entityKey');
    define('AND_PARAMETER_KEY', 'parameterkey'); // CHOOSE AND_PARAMETER_KEY or  AND_CURRENT_USERID
    define('AND_CURRENT_USERID', 'currentUserId'); // There is a precedence for AND_CURRENT_USERID

    define('TOKEN_CONTEXT_KEY', 'context');

    define('NOT_OBSERVABLE', 'NO');
    define('NO_UID', 'NU');

    define('DEFAULT_SPACE_UID', '0');  //used when there no dID by the GateKeeper

    // TOKEN KEYS
    define('DEFAULT_APP_TOKEN_KEY', 'apptoken');    // Generaly Required to secure non authenticated operations
    define('DEFAULT_AUTH_COOKIE_KEY', 'authtoken');  // Set on sign in

    // SOME CLIENT IMPLEMENTATION MAY CONSIDER NULL as a FAULT
    // SO WE PREFER TO RETURN A VOID JSON DICTIONARY instead of a void
    define('VOID_RESPONSE', "{}");

}


/**
 * Class Configuration
 * This configuration is central in Bartleby's architecture.
 *
 * It defines or implements :
 * 
 * - SearchPaths
 * - Versionning
 * - ACL rules aka. Permissions
 * - Token Cryptographic facilities
 * - Data filters.
 * - APN push confs.
 *
 * @package Bartleby\Core
 */
class Configuration {

    // APP

    // Bartleby's version
    const BARTLEBY_VERSION = "1.0";
    const BARTLEBY_RELEASE = "beta2";
    const INFORMATIONS_KEY = 'informations';
    const ANONYMOUS = 'anonymous';
    const APP_TOKEN_KEY = DEFAULT_APP_TOKEN_KEY;

    protected $_executionDirectory;
    protected $_bartlebyRootDirectory;

    protected $_HOST = NULL;
    protected $_STAGE = Stages::DEVELOPMENT;
    protected $_VERSION = 'v1';
    protected $_SECRET_KEY = 'You should define a salt to make good code soup'; // PRIVATE SALT 32BYTES MIN
    protected $_SHARED_SALT = "You should define this salt and share it with the app clients"; // SALT SHARED BY CLIENTS

    protected $_superAdmins = array();// The super admin ids are declarative
    protected $_permissionsRules = array();// Will be setup first in BartlebyCommonsConfiguration

    private $_filtersIn = array();
    private $_filtersOut = array();

    /**
     * @var array of overloaded paths.
     */
    private $_fixedPaths = array();

    /**
     * An array of issues used for analysis
     * @var array
     */
    public $issues=array();

    /**
     * @return The Host
     */
    public function HOST() {
        return $this->_HOST;
    }


    //////////////////////////////////
    // Behavioural const + getters
    ///////////////////////////////////

    // The getters allows overload of the consts.

    /**
     * Disable the ACL to perform Dev tests.
     */
    const DISABLE_ACL = false;

    /**
     * @return bool
     */
    public function DISABLE_ACL() {
        return $this::DISABLE_ACL;
    }

    /**
     * Can be used during development to simplify the tests.
     */
    const BY_PASS_SALTED_TOKENS = false;  // Should be set to false !

    /**
     * @return bool
     */
    public function BY_PASS_SALTED_TOKENS() {
        return $this::BY_PASS_SALTED_TOKENS;
    }


    /*
    * Should be used once to call destructive installer.
    */
    const ALLOW_DESTRUCTIVE_INSTALLER = false;  // Should be set to false (!)

    /**
     * @return bool
     */
    public function ALLOW_DESTRUCTIVE_INSTALLER() {
        return $this::ALLOW_DESTRUCTIVE_INSTALLER;
    }

    /**
     * Used to get more verbose response on Core issues
     */
    const DEVELOPER_DEBUG_MODE = true; // Should be set to false (!)

    /**
     * @return bool
     */
    public function DEVELOPER_DEBUG_MODE() {
        return $this::DEVELOPER_DEBUG_MODE;
    }


    /**
     * You stop encrypting cookies to search key - and crypto error during development.
     */
    const ENCRYPT_COOKIES = true;  // Should be set to true (!)

    /**
     * @return bool
     */
    public function ENCRYPT_COOKIES() {
        return $this::ENCRYPT_COOKIES;
    }

    /**
     * If set to true on multiple creation attempts
     * The exception thrown by the creation will be catched
     */
    const IGNORE_MULTIPLE_CREATION_IN_CRUD_MODE = true;

    /**
     * @return bool
     */
    public function IGNORE_MULTIPLE_CREATION_IN_CRUD_MODE() {
        return $this::IGNORE_MULTIPLE_CREATION_IN_CRUD_MODE;
    }

    /**
     * If set to true on multiple deletion attempts
     * The exception thrown by the creation will be catched
     */
    const IGNORE_MULTIPLE_DELETION_ATTEMPT = true;

    /**
     * @return bool
     */
    public function IGNORE_MULTIPLE_DELETION_ATTEMPT() {
        return $this::IGNORE_MULTIPLE_DELETION_ATTEMPT;
    }


    ////////////////////
    // APP
    ///////////////////


    /**
     * Constructor
     *
     * @param $executionDirectory string
     * @param $bartlebyRootDirectory string
     */
    public function __construct($executionDirectory, $bartlebyRootDirectory) {
        $this->_executionDirectory = $executionDirectory;
        $this->_bartlebyRootDirectory = $bartlebyRootDirectory;
    }

    /**
     * @return string
     */
    public function getExecutionDirectory() {
        return $this->_executionDirectory;
    }

    /**
     * @return mixed
     */
    public function getBartlebyRootDirectory() {
        return $this->_bartlebyRootDirectory;
    }

    /**
     * @return string
     */
    public function STAGE() {
        return $this->_STAGE;
    }

    /**
     * @return string
     */
    public function VERSION() {
        return $this->_VERSION;
    }


    public function getEntitiesName($runMode) {
        if ($runMode == Mode::PAGES) {
            return 'Pages';
        } else {
            return 'EndPoints';
        }
    }


    ////////////////////
    // Routes
    ///////////////////


    function getRouteAliases($runMode) {
        if ($runMode == Mode::PAGES) {
            return $this->_getPagesRouteAliases();
        } else {
            return $this->_getEndPointsRouteAliases();
        }
    }

    /**
     * Returns a Routes aliases
     * Should be setup first in BartlebyCommonsConfiguration
     * @return RoutesAliases
     */
    protected function _getEndPointsRouteAliases() {
        $mapping = array();// This message should never appear
        return new RoutesAliases($mapping);
    }

    /**
     * Returns a Routes aliases
     * Should be setup first in BartlebyCommonsConfiguration
     * @return RoutesAliases
     */
    protected function _getPagesRouteAliases() {
        $mapping = array();// This message should never appear
        return new RoutesAliases($mapping);
    }


    ////////////////////
    // Search Paths
    ///////////////////


    function getModelsSearchPaths() {
        return array(
            $this->_executionDirectory . $this->_VERSION . '/_generated/Models/',
            $this->_executionDirectory . $this->_VERSION . '/Models/',
            $this->_bartlebyRootDirectory . 'Commons/Models/',
            $this->_bartlebyRootDirectory . 'Commons/_generated/Models/'
        );
    }


    function getEndpointsSearchPaths() {
        return array(
            $this->_executionDirectory . $this->_VERSION . '/_generated/EndPoints/',
            $this->_executionDirectory . $this->_VERSION . '/EndPoints/',
            $this->_bartlebyRootDirectory . 'Commons/EndPoints/',
            $this->_bartlebyRootDirectory . 'Commons/_generated/EndPoints/'
        );
    }

    function getPagesSearchPaths() {
        return array(
            $this->_executionDirectory . $this->_VERSION . '/_generated/Pages/',
            $this->_executionDirectory . $this->_VERSION . '/Pages/',
            $this->_bartlebyRootDirectory . 'Commons/Pages/',
            $this->_bartlebyRootDirectory . 'Commons/_generated/Pages/'
        );
    }


    /**
     * Define the path for a given class.
     * This method allows fast search resolution and class overload. (hook)
     *
     * @param string $className string the class name.
     * @param string $path  the absolute path
     */
    function definePath($className,$path){
        $this->_fixedPaths[$className]=$path;
    }

    /**
     * Return the path if defined else a "" string
     * @param string $className the class name
     * @return mixed|string
     */
    function getFixedPathForClassName($className=""){
        if (array_key_exists($className,$this->_fixedPaths)){
            return $this->_fixedPaths[$className];
        }else{
            return "";
        }
    }

    ////////////////////
    // Permissions
    ///////////////////


    final function getSuperAdminUIDS() {
        return $this->_superAdmins;
    }


    final function getPermissionsRules() {
        return $this->_permissionsRules;
    }


    protected function addSuperAdminUIDs(array $udids) {
        foreach ($udids as $udid) {
            $this->_superAdmins[] = $udid;
        }
    }


    protected function addPermissions(array $permission) {
        foreach ($permission as $name => $value) {
            $this->_permissionsRules[$name] = $value;
        }
    }


    ////////////////////////
    // COOKIES
    ////////////////////////


    function  getAuthCookieKEYForRUID($spaceUID) {
        if ($this->ENCRYPT_COOKIES()){
            return $this->salt($spaceUID);
        }else{
            return $spaceUID;
        }

    }

    function getCryptedAuthCookieValue($spaceUID, $userID) {
        if ($this->ENCRYPT_COOKIES()){
        // We use $this->_SECRET_KEY truncated to 32Bytes as key
        // We use $spaceUID as Initialization vector
         return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_32SALT(), $userID, MCRYPT_MODE_ECB, $spaceUID);
        }else{
            return $userID;
        }
    }

    function decryptAuthCookieValue($spaceUID, $cryptedUserID) {
        if ($this->ENCRYPT_COOKIES()){
        // We use $this->_SECRET_KEY truncated to 32Bytes as key
        // We use $spaceUID as Initialization vector
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_32SALT(), $cryptedUserID, MCRYPT_MODE_ECB, $spaceUID);
        }else{
            return $cryptedUserID;
        }
    }

    /**
     * $this->_SECRET_KEY truncated to 32Bytes as key
     * @return string
     */
    protected function _32SALT() {
        return substr($this->_SECRET_KEY, 0, 32);
    }

    /**
     * Returns the userID from the cookie
     * @param $spaceUID
     * @return null|string
     */
    function getUserIDFromCookie($spaceUID) {
        if (!isset($spaceUID)){
            $this->issues[]='dID is not set';
        }
        if (!isset($_COOKIE)){
            $this->issues[]='Php\'s _COOKIE global is not existing.';
        }
        $cookieKey = $this->getAuthCookieKEYForRUID($spaceUID);
        if (array_key_exists($cookieKey, $_COOKIE)) {
            $cookieValue = $_COOKIE[$cookieKey];
            $userID = $this->decryptAuthCookieValue($spaceUID, $cookieValue);
            return $userID;
        }else{
            $this->issues[]='Cookie key '.$cookieKey.' is not existing. ';
            $this->issues[]='_COOKIE=['.implode(',',$_COOKIE).'] ';
        }
        return NULL;
    }

    /**
     * return true if there is a consistant cookie for the context
     * @param $spaceUID
     * @return bool
     */
    function hasUserAuthCookie($spaceUID) {
        $cookieKey = $this->getAuthCookieKEYForRUID($spaceUID);
        return (array_key_exists($cookieKey, $_COOKIE));
    }

    ////////////////////
    // FILTERS
    ///////////////////


    function addFilterIn($endPointKey, IFilter $filter) {
        if (isset($endPointKey) && isset($filter)) {
            $this->_filtersIn[$endPointKey] = $filter;
        }
    }

    function addFilterOut($endPointKey, IFilter $filter) {
        if (isset($endPointKey) && isset($filter)) {
            $this->_filtersOut[$endPointKey] = $filter;
        }
    }

    function hasFilterOUT($endPointKey) {
        return (array_key_exists($endPointKey, $this->_filtersOut));
    }

    function hasFilterIN($endPointKey) {
        return (array_key_exists($endPointKey, $this->_filtersIn));
    }

    function runFilterIN($endPointKey, $parameters) {
        /* @var $filter IFilter */
        $filter = $this->_filtersIn[$endPointKey];
        return $filter->filterData($parameters);

    }

    function runFilterOUT($endPointKey, $responseData) {
        /* @var $filter IFilter */
        $filter = $this->_filtersOut[$endPointKey];
        return $filter->filterData($responseData);
    }


    ////////////////////
    // SALT
    ///////////////////

    function salt($string) {
        return md5($string . $this->_SECRET_KEY);
    }

    function saltWithSharedKey($string) {
        return md5($string . $this->_SHARED_SALT);
    }


    ////////////////////
    // APN
    ///////////////////


    // APN
    protected $_APN_PASS_PHRASE = '';
    protected $_APN_PORT = 2195;


    /**
     * @return string m
     */
    public function APN_PASS_PHRASE() {
        return $this->_APN_PASS_PHRASE;
    }

    /**
     * @return string
     */
    public function APN_HOST() {
        if ($this->STAGE() == 'production') {
            return 'tls://gateway.push.apple.com';
        } else {
            return 'tls://gateway.sandbox.push.apple.com';
        }
    }

    /**
     * @return int
     */
    public function APN_PORT() {
        return $this->_APN_PORT;
    }

    /**
     * @return string
     */
    public function APN_CERTIFICATE_PATH() {
        return $this->_executionDirectory . '/resources/' . $this->STAGE() . '.pem';
    }

    /**
     * @return string
     */
    public function APN_CERTIFICATE_AUTHORITY_PATH() {
        return $this->_executionDirectory . '/resources/entrust_2048_ca.cer';
    }

}