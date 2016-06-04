<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'GeneratedConfiguration.php';
    $f->package =  'php/api/'.$h->majorVersionPathSegmentString().'_generated/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

// SHARED CONFIGURATION BETWEEN THE API & MAIN PAGES

namespace Bartleby;

require_once BARTLEBY_ROOT_FOLDER . 'Commons/_generated/BartlebyCommonsConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';

use Bartleby\Core\RoutesAliases;
use Bartleby\Core\Stages;
use Bartleby\Mongo\MongoConfiguration;

class GeneratedConfiguration extends BartlebyCommonsConfiguration {


    protected function _configurePermissions(){

        $permissionsRules = array(
<?php
$permissionHistory=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */
while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExlcuded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExlcuded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExlcuded = true;
            }
        }
    }


    if($shouldBeExlcuded==true){
        continue;
    }

    $path=$action->path;
    $path=ltrim($path,'/');
    $classNameWithoutPrefix=ucfirst(substr($action->class,strlen($d->classPrefix)));


    //$string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_BY_TOKEN,TOKEN_CONTEXT=>'$classNameWithoutPrefix#rUID')";
    $string= "'".$classNameWithoutPrefix."->call'=>array('level' => PERMISSION_IDENTIFIED_BY_COOKIE)";

    if(!$d->lastAction()){
        $string.=',';
    }
    if(!in_array($string,$permissionHistory)){
        $permissionHistory[]=$string;
        echoIndentCR($string,2);
    }
}
?>      );
        $this->addPermissions($permissionsRules);
    }

/*
    In your Configuration you can override the aliases.

    protected function _getPagesRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        ''=>'Start',
        'time'=>'Time',
        '*' => 'NotFound'
        );
        $routes->addAliasesToMapping($mapping);
    return $routes;
    }

    protected function _getEndPointsRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
        'POST:/user/{userId}/comments'=>array('CommentsByUser','POST_method_for_demo'),
        'DELETE:/user/{userId}/comments'=>array('CommentsByUser','DELETE'),
        'time'=>'SSETime' // A server sent event sample
        );
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }


*/

    protected function _getEndPointsRouteAliases () {
        $routes=parent::_getEndPointsRouteAliases();
        $mapping = array(
<?php
$history=array();
/* @var $d ProjectRepresentation */
/* @var $action ActionRepresentation */

while ($d->iterateOnActions() ) {

    $action=$d->getAction();
    $shouldBeExlcuded=false;
    foreach ($h->excludePath as $pathToExclude ) {
        if(strpos($action->class.'.php',$pathToExclude)!==false){
            $shouldBeExlcuded=true;
        }
    }
    if (isset($excludeActionsWith)) {
        foreach ($excludeActionsWith as $actionTobeExcluded ) {
            if (strpos($action->class, $actionTobeExcluded) !== false) {
                $shouldBeExlcuded = true;
            }
        }
    }

    if($shouldBeExlcuded==true){
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
        $routes->addAliasesToMapping($mapping);
        return $routes;
    }
}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>