<?php

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

// Where is Bartleby?
$baseDirectory = (dirname(__DIR__));
define('BARTLEBY_ROOT_FOLDER', $baseDirectory . '/Bartleby/');
define('BARTLEBY_PUBLIC_FOLDER',__DIR__ .'/');

require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterEntityPasswordRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterCollectionOfEntityPasswordsRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterHookByClosure.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Stages.php';

require_once __DIR__ . '/api/v1/_generated/GeneratedConfiguration.php';

use Bartleby\Core\Stages;
use Bartleby\GeneratedConfiguration;

class Configuration extends GeneratedConfiguration {

    ////////////////////
    // BEHAVIORAL CONSTS
    ///////////////////

    /*
     * Can be used during development to simplify the tests.
     */
    const BY_PASS_SALTED_TOKENS = false;

    /*
    * Should be used once to call destructive installer.
    */
    const ALLOW_DESTRUCTIVE_INSTALLER = false;

    /**
     * Used to get more verbose response on Core
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
    public function __construct($executionDirectory, $bartlebyRootDirectory) {
        parent::__construct($executionDirectory, $bartlebyRootDirectory);
        $this->_configure();
    }


    private function _configure() {

        /////////////////////////
        // APP configuration
        /////////////////////////

        $this->_STAGE = Stages::DEVELOPMENT;
        $this->_VERSION = 'v1';
        $this->_SECRET_KEY = 'AP_SK'; // 32 Bytes min
        $this->_SHARED_SALT='AP_PSS'; // 32 Bytes min

        /////////////////////////
        // MONGO DB
        /////////////////////////

        $this->_MONGO_DB_NAME = 'AP_MDN';


        $this->_configurePermissions();

        /////////////////////////
        // PARSE parameters.json
        /////////////////////////

        try{
            $path=__DIR__.'/Protected/parameters.json';
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
        $permissions = array(

            // Check the Generated configuration
            // All the calls are set by default to PERMISSION_BY_TOKEN
            // Including AUTHENTICATION

            // You can add or modify permissions here.
            // If you declare a rule it will replace the generated rule.

            // Un comment
            //'CreateUser->call'=>array('level' =>PERMISSION_NO_RESTRICTION),

            //SSE Time
            'SSETime->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),

            // Pages
            'Start->GET'=>array('level' => PERMISSION_NO_RESTRICTION),
            'Time->GET'=>array('level' => PERMISSION_NO_RESTRICTION),

            // BartlebySync
            'BartlebySyncSupports->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncInstall->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncCreateTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncTouchTree->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncGetHashMap->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncGetFile->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncUploadFileTo->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncFinalizeTransactionIn->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'BartlebySyncFinalizeTransactionIn->cleanUp'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),// May be suspended ( it is used on finalizeTransactionIn)
            'BartlebySyncRemoveGhosts->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY)
        );
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
            // BartlebySync
            'GET:/BartlebySync/isSupported'=>array('BartlebySyncSupports','call'),
            'GET:/BartlebySync/reachable'=>array('Reachable','GET'),
            'POST:/BartlebySync/install'=>array('BartlebySyncInstall','call'),
            'POST:/BartlebySync/create/tree/{treeId}'=>array('BartlebySyncCreateTree','call'),
            'POST:/BartlebySync/touch/tree/{treeId}'=>array('BartlebySyncTouchTree','call'),
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