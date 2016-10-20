<?php

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

// Where is Bartleby?
$baseDirectory = (dirname(__DIR__));

if (!defined('BARTLEBY_ROOT_FOLDER')){
    define('BARTLEBY_ROOT_FOLDER', $baseDirectory . '/Bartleby/');
    define('BARTLEBY_PUBLIC_FOLDER',__DIR__ .'/');
}

require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterEntityPasswordRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterCollectionOfEntityPasswordsRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterHookByClosure.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Stages.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Mode.php';

require_once __DIR__ . '/api/v1/_generated/GeneratedConfiguration.php';

use Bartleby\Core\Mode;
use Bartleby\Core\Stages;
use Bartleby\GeneratedConfiguration;

class Configuration extends GeneratedConfiguration {


    ////////////////////
    // BEHAVIORAL CONSTS
    ///////////////////

    /**
     * Disable the ACL to perform Dev tests.
     */
    const DISABLE_ACL = false; // Should be set to false!

    /**
     * Can be used during development to simplify the tests.
     */
    const BY_PASS_SALTED_TOKENS = false;  // Should be set to false!

    /**
     * Should be used once to call destructive installer.
     */
    const ALLOW_DESTRUCTIVE_INSTALLER = true; // Should be set to false!

    /**
     * Disables the data Filters IN & OUT
     * This options should never be turned to true in production.
     * The passwords would be  server side stored
     */
    const DISABLE_DATA_FILTERS = false;  // Should be set to false!

    /**
     * Used to get more verbose responses when debugging
     */
    const DEVELOPER_DEBUG_MODE = true;

    /**
     * If set to true on multiple creation attempts
     * The exception thrown by the creation will be catched
     */
    const IGNORE_MULTIPLE_CREATION_IN_CRUD_MODE = true;

    /**
     * @param string
     * @param string
     */
    public function __construct($executionDirectory, $bartlebyRootDirectory,$runMode = Mode::API) {
        parent::__construct($executionDirectory, $bartlebyRootDirectory,$runMode);
        $this->_configure();
    }


    private function _configure() {

        /////////////////////////
        // APP configuration
        /////////////////////////

        $baseUrlPerStages = array(
            Stages::DEVELOPMENT => 'FLOCKED_API_URL_DEVELOPMENT',
            Stages::PRODUCTION => 'FLOCKED_API_URL_PRODUCTION',
            Stages::ALTERNATIVE => 'FLOCKED_API_URL_ALTERNATIVE',
            Stages::LOCAL => 'http://localhost:8001/',
        );

        $this->_autoDefineBaseUrlAndStage($baseUrlPerStages);

        //
        // YOU SHOULD PROVIDE ONE CONFIGURATION SET PER STAGE
        //

        if ($this->_STAGE == Stages::DEVELOPMENT) {
            $this->_VERSION = 'v1';
            $this->_SECRET_KEY = 'FLOCKED_SECRET_KEY_DEVELOPMENT'; // 32 Bytes min
            $this->_SHARED_SALT = 'FLOCKED_SHARED_SALT_DEVELOPMENT';
            if (!defined('BARTLEBY_SYNC_SECRET_KEY')){
                define('BARTLEBY_SYNC_SECRET_KEY', 'FLOCKED_BARTLEBY_SYNC_SECRET_KEY_DEVELOPMENT'); // Used create the data system folder
            }
        } elseif ($this->_STAGE == Stages::PRODUCTION) {
            $this->_VERSION = 'v1';
            $this->_SECRET_KEY = 'FLOCKED_SECRET_KEY_PRODUCTION'; // 32 Bytes min
            $this->_SHARED_SALT = 'FLOCKED_SHARED_SALT_PRODUCTION';
            if (!defined('BARTLEBY_SYNC_SECRET_KEY')){
                define('BARTLEBY_SYNC_SECRET_KEY', 'FLOCKED_BARTLEBY_SYNC_SECRET_KEY_PRODUCTION'); // Used create the data system folder
            }
        } elseif ($this->_STAGE == Stages::ALTERNATIVE) {
            $this->_VERSION = 'v1';
            $this->_SECRET_KEY = 'FLOCKED_SECRET_KEY_ALTERNATIVE'; // 32 Bytes min
            $this->_SHARED_SALT = 'FLOCKED_SHARED_SALT_ALTERNATIVE';
            if (!defined('BARTLEBY_SYNC_SECRET_KEY')){
                define('BARTLEBY_SYNC_SECRET_KEY', 'FLOCKED_BARTLEBY_SYNC_SECRET_KEY_ALTERNATIVE'); // Used create the data system folder
            }
        } else {
            $this->_VERSION = 'v1';
            $this->_SECRET_KEY = 'FLOCKED_SECRET_KEY_DEFAULT'; // 32 Bytes min
            $this->_SHARED_SALT = 'FLOCKED_SHARED_SALT_DEFAULT';
            if (!defined('BARTLEBY_SYNC_SECRET_KEY')){
                define('BARTLEBY_SYNC_SECRET_KEY', 'FLOCKED_BARTLEBY_SYNC_SECRET_KEY_DEFAULT'); // Used create the data system folder
            }
        }

        if (!defined('REPOSITORY_BASE_URL')){
            define('REPOSITORY_BASE_URL', $baseUrlPerStages[$this->_STAGE]);
            define('REPOSITORY_WRITING_PATH',__DIR__. '/files/');

        }

        /////////////////////////
        // MONGO DB
        /////////////////////////

        $this->_MONGO_DB_NAME = 'youDubDev';

        $this->_configurePermissions();

        /////////////////////////
        // PARSE parameters.json
        /////////////////////////

        try{
            $path= BARTLEBY_PUBLIC_FOLDER . '/Protected/parameters.json';
            $string=file_get_contents($path);
            if(isset($string)){
                $conf=json_decode($string,true);
                if(is_array($conf)){
                    if (array_key_exists("superAdmins",$conf)){
                        $sAdmins=$conf["superAdmins"];
                        if (is_array($sAdmins)){
                            $this->addSuperAdminUIDs($sAdmins);
                        }
                    }
                }
            }
        }catch (\Exception $e){
            // Silent catch
        }
    }


    protected function _configurePermissions(){

        parent::_configurePermissions();


        $permissions = [
            // Check the Generated configuration
            // All the calls are set by default to PERMISSION_BY_TOKEN
            // Including AUTHENTICATION

            // You can add or modify permissions here.
            // If you declare a rule it will replace the generated rule.

            // Pages
            'Start->GET'=>array('level' => PERMISSION_NO_RESTRICTION),
            'Time->GET'=>array('level' => PERMISSION_NO_RESTRICTION),
            'Triggers->GET' => array('level' => PERMISSION_NO_RESTRICTION),
            'Tools->GET' => array('level' => PERMISSION_NO_RESTRICTION),
            'SignIn->GET' => array('level' => PERMISSION_NO_RESTRICTION),
            'SignOut->GET' => array('level' => PERMISSION_NO_RESTRICTION),
            'SignIn->POST' => array('level' => PERMISSION_BY_TOKEN),

            //'ProtectedRun->GET'=>array('level' => PERMISSION_NO_RESTRICTION),

            'EntityExistsById->call'=>array('level' => PERMISSION_NO_RESTRICTION),//TEMP

            // BartlebySync
            'BartlebySyncSupports->call'=>array('level' => PERMISSION_NO_RESTRICTION),
            'BartlebySyncInstall->call'=>array('level' => PERMISSION_NO_RESTRICTION),
            'BartlebySyncCreateTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncDeleteTree->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'BartlebySyncTouchTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncGetHashMap->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncGetFile->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncUploadFileTo->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncFinalizeTransactionIn->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncFinalizeTransactionIn->cleanUp'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),// May be suspended ( it is used on finalizeTransactionIn)
            'BartlebySyncRemoveGhosts->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY)
        ];

        $this->addPermissions($permissions);
    }



    /////////////////////////
    // Pages aliases
    /////////////////////////

    protected function _getPagesRouteAliases() {
        $routes = parent::_getPagesRouteAliases();
        $mapping = array(
            '' => 'Start',
            'time' => 'Time',
            'triggers'=>'Triggers',
            'tools'=>'Tools',
            'signIn'=>'SignIn',
            'signOut' => "SignOut",
            '*' => 'NotFound'
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }

    /////////////////////////
    // End points aliases
    /////////////////////////
    protected function  _getEndPointsRouteAliases() {
        $routes = parent::_getEndPointsRouteAliases();
        $mapping = array(
            //'POST:/user/{userId}/comments'=>array('CommentsByUser','POST_method_for_demo'),
            //'DELETE:/user/{userId}/comments'=>array('CommentsByUser','DELETE'),
            'time' => 'SSETime', // A server sent event sample
            'infos' => 'Infos',
            'GET:/run' => 'ProtectedRun',
            // BartlebySync
            'GET:/BartlebySync/isSupported'=>array('BartlebySyncSupports','call'),
            'GET:/BartlebySync/reachable'=>array('Reachable','GET'),
            'POST:/BartlebySync/install'=>array('BartlebySyncInstall','call'),
            'POST:/BartlebySync/create/tree/{treeId}'=>array('BartlebySyncCreateTree','call'),
            'POST:/BartlebySync/touch/tree/{treeId}'=>array('BartlebySyncTouchTree','call'),
            'DELETE:/BartlebySync/delete/tree/{treeId}'=>array('BartlebySyncDeleteTree','call'),
            'GET:/BartlebySync/tree/{treeId}'=>array('BartlebySyncTouchTree','call'),//touch alias
            'GET:/BartlebySync/hashMap/tree/{treeId}'=>array('BartlebySyncGetHashMap','call'),
            'GET:/BartlebySync/file/tree/{treeId}'=>array('BartlebySyncGetFile','call'),
            'POST:/BartlebySync/uploadFileTo/tree/{treeId}'=>array('BartlebySyncUploadFileTo','call'),
            'POST:/BartlebySync/finalizeTransactionIn/tree/{treeId}'=>array('BartlebySyncFinalizeTransactionIn','call'),
            'POST:/BartlebySync/cleanUp/tree/{treeId}'=>array('BartlebySyncFinalizeTransactionIn','cleanUp'), // May be suspended ( it is used on finalizeTransactionIn)
            'POST:/BartlebySync/removeGhosts'=>array('BartlebySyncRemoveGhosts','call')
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }


    // ################
    // ### ADVANCED ###
    // ################


    /////////////////////////
    // SEARCH PATHS
    /////////////////////////

    function getEndpointsSearchPaths() {
        $searchPaths = parent::getEndpointsSearchPaths();
        // You can add your own search paths if necessary
        // The search paths are absolute.
        // BartlebySync
        $searchPaths[]=dirname(dirname($this->_executionDirectory)).'/BartlebySync/EndPoints/';
        return $searchPaths;
    }

    function getPagesSearchPaths() {
        $searchPaths = parent::getPagesSearchPaths();
        // You can add your own search paths if necessary
        // The search paths are absolute.
        return $searchPaths;
    }

    function getModelsSearchPaths() {
        $searchPaths = parent::getModelsSearchPaths();
        // You can add your own search paths if necessary
        // The search paths are absolute.
        return $searchPaths;
    }

}