<?php

namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/KeyPath.php';

use Bartleby\Core\Configuration;
use Bartleby\Core\JsonResponse;
use Bartleby\Core\Mode;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\KeyPath;


final class InfosCallData extends MongoCallDataRawWrapper {

    // Set a key if you want only that key.
    const k = "k";
}

/**
 * Class Infos
 *
 * You can grab :
 *
 * All the infos
 * http://localhost/api/v1/infos
 *
 * Unique key
 * http://localhost/api/v1/infos?k=acl_is_disabled
 *
 * Nested key path
 * http://localhost/api/v1/infos?k=configuration.STAGE
 * http://localhost/api/v1/infos?k=configuration.getPagesSearchPaths.0
 *
 *
 * @package Bartleby\EndPoints
 */
final class Infos extends MongoEndPoint {

    function GET(){
        /* @var InfosCallData */
        $parameters=$this->getModel();
        $infos=array();
        
        // Configuration

        $infos["version_of_Bartleby"]=Configuration::BARTLEBY_VERSION.'.'.Configuration::BARTLEBY_RELEASE;
        $infos["acl_is_disabled"] = $this->getConfiguration()->DISABLE_ACL();
        $infos["configuration"]=array();
        $infos["configuration"]["STAGE"]=$this->getConfiguration()->STAGE();
        $infos["configuration"]["VERSION"]=$this->getConfiguration()->VERSION();
        $infos["configuration"]["getBartlebyRootDirectory"]=$this->getConfiguration()->getBartlebyRootDirectory();
        $infos["configuration"]["getEndpointsSearchPaths"]=$this->getConfiguration()->getEndpointsSearchPaths();
        $infos["configuration"]["getPagesSearchPaths"]=$this->getConfiguration()->getPagesSearchPaths();
        $infos["configuration"]["getPermissionsRules"]=$this->getConfiguration()->getPermissionsRules();
        $infos["configuration"]["getRouteAliases(API)"]=$this->getConfiguration()->getRouteAliases(Mode::API)->getMapping();
        $infos["configuration"]["getRouteAliases(PAGES)"]=$this->getConfiguration()->getRouteAliases(Mode::PAGES)->getMapping();

        $key = $parameters->getValueForKey(InfosCallData::k);
        if (isset($key)) {
            $value = KeyPath::valueForKeyPath($infos, $key);
            if (isset($value)) {
                $infos = array($key => $value);
            } else {
                $infos = 'Key not found "' . $key . '"';
            }
        }

        return new JsonResponse($infos, 200);
    }


}