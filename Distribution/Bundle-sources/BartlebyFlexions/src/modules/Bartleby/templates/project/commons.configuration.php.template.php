<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'BartlebyCommonsConfiguration.php';
    $f->package = 'php/_generated/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterEntityPasswordRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterCollectionOfEntityPasswordsRemover.php';
require_once BARTLEBY_ROOT_FOLDER . 'Commons/Filters/FilterHookByClosure.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Stages.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Mode.php';


use Bartleby\Core\Mode;
use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;
use Bartleby\Core\KeyPath;
use Bartleby\Filters\FilterCollectionOfEntityPasswordsRemover;
use Bartleby\Filters\FilterEntityPasswordRemover;
use Bartleby\Filters\FilterHookByClosure;

use \MongoClient;
use \MongoCursorException;
use \MongoDB;


class BartlebyCommonsConfiguration extends MongoConfiguration {


    /**
    * The constructor
    * @param string $executionDirectory
    * @param string $bartlebyRootDirectory
    * @param $runMode
    */
    public function __construct($executionDirectory,$bartlebyRootDirectory,$runMode = Mode::API){
        parent::__construct($executionDirectory,$bartlebyRootDirectory,$runMode);
        $this->_configureFilters();
        $this->_configurePermissions();
        $this->_configuresFixedPaths();
    }

    private function  _configuresFixedPaths(){
        // We force the resolution
        // So You can Overload the standard path and define a fixed One
        // To to so you can call `definePath`:
        // $this->definePath("ClassName", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/ClassName.php');`

        // (!) IMPORTANT
        // If you put files in the Overloads folder that extends an existing class.
        // The nameSpace of the Overload must be post fixed with \Overloads
        // Check UpdateUser for a sample.

        // Update user(s) overload for security purposes.
        $this->definePath("UpdateUser", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/UpdateUser.php');
        $this->definePath("UpdateUsers", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/UpdateUsers.php');
    }

    private function _configureFilters(){

        //NEVER DISCLOSE THE PASSWORDS!

        $filterReadUser=new FilterEntityPasswordRemover();
        $filterReadUser->passwordKeyPath='password';
        $this->addFilterOut('ReadUserById->call',$filterReadUser);

        $filterReadUsers=new FilterCollectionOfEntityPasswordsRemover();
        $filterReadUsers->passwordKeyPath='password';// Each entity has directly a "password" key
        $filterReadUsers->iterableCollectionKeyPath=NULL;// the response is a collection.
        $this->addFilterOut('ReadUsersByIds->call',$filterReadUsers);


        // Salt the passwords on Create and Update

        $data=NULL;// Dummy data for the IDE

        $filterCreateUser=new FilterHookByClosure();
        $filterCreateUser->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            // let's salt the password
            KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            return $data;
        };
        $this->addFilterIn('CreateUser->call',$filterCreateUser);

        $filterCreateUsers=new FilterHookByClosure();
        $filterCreateUsers->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            // let's salt the password
            KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            return $data;
        };
        $this->addFilterIn('CreateUsers->call',$filterCreateUsers);

        $filterUpdateUser=new FilterHookByClosure();
        $filterUpdateUser->closure=function($data) {
            // let's salt the password
            $password=KeyPath::valueForKeyPath($data,"user.password");
            KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            return $data;
        };
        $this->addFilterIn('UpdateUser->call',$filterUpdateUser);

        $filterUpdateUsers=new FilterHookByClosure();
        $filterUpdateUsers->closure=function($data) {
            $password=KeyPath::valueForKeyPath($data,"user.password");
            // let's salt the password
            KeyPath::setValueByReferenceForKeyPath($data,"user.password",$this->salt($password));
            return $data;
        };
        $this->addFilterIn('UpdateUsers->call',$filterUpdateUsers);
    }
    /**
    * Configure the permissions
    * By default we provide a good level of security
    */
    private function _configurePermissions(){

        $this->_permissionsRules = array(


            'NotFound->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            'Reachable->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            'Reachable->verify'=> array('level'=> PERMISSION_BY_IDENTIFICATION),
            'Auth->POST' => array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'LoginUser#spaceUID'),// (!) do not change
            'Auth->DELETE' => array('level'  => PERMISSION_NO_RESTRICTION), // (!)
            //SSE Time
            'SSETime->GET'=> array('level'=> PERMISSION_NO_RESTRICTION),
            //SSE Trigggers
            'SSETriggers->GET' => array('level' => PERMISSION_NO_RESTRICTION),
            // ProtectedRun
            'ProtectedRun->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            // The configuration infos endpoint
            'Infos->GET'=>array('level' => PERMISSION_NO_RESTRICTION),

            // USERS

            'ReadUserById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'CreateUser->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'CreateUser#spaceUID'),

            'UpdateUser->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
					ARRAY_OF_QUERIES =>array(
						"hasBeenCreatedByCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'creatorUID',
							AND_CURRENT_USERID=>true
						),
						"isCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'_id',
							AND_CURRENT_USERID=>true
					)
                )
            ),

            'DeleteUser->call'=>array(
                'level' => PERMISSION_RESTRICTED_BY_QUERIES,
                    ARRAY_OF_QUERIES =>array(
                        "hasBeenCreatedByCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'creatorUID',
							AND_CURRENT_USERID=>true
						),
                        "isCurrentUser"=>array(
							SELECT_COLLECTION_NAME=>'users',
							WHERE_VALUE_OF_ENTITY_KEY=>'_id',
							EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

							COMPARE_WITH_OPERATOR=>'==',
							RESULT_ENTITY_KEY=>'_id',
							AND_CURRENT_USERID=>true
						)
                	)
            )
            ,
            'CreateUsers->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadUsersByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'UpdateUsers->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'DeleteUsers->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadUsersByQuery->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),


            // Locker

			/*
				1# A distant locker can be accessed only by Authenticated users.
     			2# A Locker can be "Created Updated Deleted" only by its creator. Locker.creatorUID
     			3# A locker cannot be read distantly but only verifyed
     			4# On successful verification the locker is returned with its cake :)
			*/

			'VerifyLocker->POST' => array('level' => PERMISSION_BY_IDENTIFICATION),
			'CreateLocker->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
			'UpdateLocker->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
				ARRAY_OF_QUERIES =>array(
					"hasBeenCreatedByCurrentUser"=>array(
						SELECT_COLLECTION_NAME=>'lockers',
						WHERE_VALUE_OF_ENTITY_KEY=>'_id',
						EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'user._id',

						COMPARE_WITH_OPERATOR=>'==',
						RESULT_ENTITY_KEY=>'creatorUID',
						AND_CURRENT_USERID=>true
					)
				)
			),
			'DeleteLocker->call'=>array(
				'level' => PERMISSION_RESTRICTED_BY_QUERIES,
				ARRAY_OF_QUERIES =>array(
					"hasBeenCreatedByCurrentUser"=>array(
						SELECT_COLLECTION_NAME=>'lockers',
						WHERE_VALUE_OF_ENTITY_KEY=>'_id',
						EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'lockerId',

						COMPARE_WITH_OPERATOR=>'==',
						RESULT_ENTITY_KEY=>'creatorUID',
						AND_CURRENT_USERID=>true
					)
				)
			),
			'ReadLockerById->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'ReadLockersByIds->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'UpdateLockers->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'DeleteLockers->call'=>array('level' => PERMISSION_IS_BLOCKED),
			'ReadLockersByQuery->call'=>array('level' => PERMISSION_IS_BLOCKED),


            // GROUPS

            'ReadGroupById->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'CreateGroup->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'UpdateGroup->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'DeleteGroup->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'CreateGroups->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadGroupsByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'UpdateGroups->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'DeleteGroups->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadGroupsByQuery->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),

            // DYNAMIC PERMISSIONS

            'ReadPermissionById->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'ReadPermissionById#spaceUID'),
            'CreatePermission->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'CreatePermission#spaceUID'),

            'UpdatePermission->call'=>array(
                'level' => PERMISSION_RESTRICTED_BY_QUERIES,
                ARRAY_OF_QUERIES =>array(
                    "hasBeenCreatedByCurrentUser"=>array(
                        SELECT_COLLECTION_NAME=>'users',
                        WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                        EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

                        COMPARE_WITH_OPERATOR=>'==',
                        RESULT_ENTITY_KEY=>'creatorUID',
                        AND_CURRENT_USERID=>true
					)
                )
			),
			'DeletePermission->call'=>array(
                        'level' => PERMISSION_RESTRICTED_BY_QUERIES,
                        ARRAY_OF_QUERIES =>array(
                        "hasBeenCreatedByCurrentUser"=>array(
                        SELECT_COLLECTION_NAME=>'users',
                        WHERE_VALUE_OF_ENTITY_KEY=>'_id',
                        EQUALS_VALUE_OF_PARAMETERS_KEY_PATH=>'userId',

                        COMPARE_WITH_OPERATOR=>'==',
                        RESULT_ENTITY_KEY=>'creatorUID',
                        AND_CURRENT_USERID=>true
                    )
                )
            ),
            'CreatePermissions->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadPermissionsByIds->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'ReadPermissionsByIds#spaceUID'),
            'UpdatePermissions->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'DeletePermissions->call'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            'ReadPermissionsByQuery->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'ReadPermissionsByQuery#spaceUID'),


            // Nobody can delete triggers.

            'SSETriggers->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'TriggerAfterIndex->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'TriggerForIndexes->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),
            'TriggersByIds->call'=>array('level' => PERMISSION_BY_IDENTIFICATION),


            // Import export special Endpoints

            //'Import->GET'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY),
            //'Export->GET'=>array('level' => PERMISSION_IS_GRANTED_TO_SUPER_ADMIN_ONLY)

            'Import->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION),// TEMP THE ACL SHOULD BE REQUALIFIED (!)
            'Export->GET'=>array('level' => PERMISSION_BY_IDENTIFICATION)// TEMP THE ACL SHOULD BE REQUALIFIED (!)


<?php
echoIndentCR("/*",2);
$permissionHistory=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */


while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExcluded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExcluded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }

    if($shouldBeExcluded==true){
        continue;
    }

    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));


    //$string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'$classNameWithoutPrefix#rUID')";
    $string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_BY_IDENTIFICATION)";

    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$permissionHistory)){
        $permissionHistory[]=$string;
        echoIndentCR($string,3);
    }
}
echoIndentCR("*/",2);
?>      );
    }

    /**
    * Setups a returns the commons Routes aliases
    * @return RoutesAliases
    */
    protected function _getEndPointsRouteAliases () {
        $mapping = array(
            'POST:/user/login' => array('Auth','POST'),
            'POST:/user/logout' => array('Auth','DELETE'), // Will call explicitly DELETE (equivalent to explicit call of DELETE login)
            'GET:/verify/credentials' => array('Reachable','verify'),
            'POST:/locker/verify' => array('VerifyLocker','POST'),
            'GET:/{spaceUID}/triggers/after/{lastIndex}' => array('TriggerAfterIndex','call'),// Multi route test
            'GET:/triggers/after/{lastIndex}' => array('TriggerAfterIndex','call'),
<?php
$history=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */

while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExcluded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExcluded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }

    if($shouldBeExcluded==true){
        continue;
    }
    
    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));
    $string= '\''.$action->httpMethod.':/'.lcfirst($path).'\'=>array(\''.$classNameWithoutPrefix.'\',\'call\')';
    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$history)){
        $history[]=$string;
        echoIndentCR($string,3);
    }
}
?>
        );
        return new RoutesAliases($mapping);
    }


    /**
    * Returns the collection name list
    * @return array
    */
    public function getCollectionsNameList(){
        $list=super::getCollectionsNameList();
<?php while ($d->iterateOnEntities() ) {
    $entity=$d->getEntity();
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }
    $shouldBeExcluded=false;
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos(strtolower($name), strtolower($actionTobeExcluded)) !== false) {
                $shouldBeExcluded = true;
            }
        }
    }
    if (!isset($name) || $name=="" ||$shouldBeExcluded==true){
        continue;
    }else{
        echoIndentCR(' $list [] = "'.strtolower(Pluralization::pluralize($name)).'";',2);
    }
}
?>
        return $list;
    }

}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>