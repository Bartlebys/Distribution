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
 * http://yd.local/api/v1/infos
 *
 * Unique key
 * http://yd.local/api/v1/infos?k=acl_is_disabled
 *
 * Nested key path
 * http://yd.local/api/v1/infos?k=configuration.STAGE
 * http://yd.local/api/v1/infos?k=configuration.getPagesSearchPaths.0
 *
 *
 * @package Bartleby\EndPoints
 */
final class Infos extends MongoEndPoint {

    function GET(InfosCallData $parameters){
        $infos=array();

        // Configuration

        $infos["version_of_Bartleby"]=Configuration::BARTLEBY_VERSION.'.'.Configuration::BARTLEBY_RELEASE;
        $infos["acl_is_disabled"] = $this->_configuration->DISABLE_ACL();
        $infos["configuration"]=array();
        $infos["configuration"]["STAGE"]=$this->_configuration->STAGE();
        $infos["configuration"]["VERSION"]=$this->_configuration->VERSION();
        $infos["configuration"]["getBartlebyRootDirectory"]=$this->_configuration->getBartlebyRootDirectory();
        $infos["configuration"]["getEndpointsSearchPaths"]=$this->_configuration->getEndpointsSearchPaths();
        $infos["configuration"]["getPagesSearchPaths"]=$this->_configuration->getPagesSearchPaths();
        $infos["configuration"]["getPermissionsRules"]=$this->_configuration->getPermissionsRules();
        $infos["configuration"]["getRouteAliases(API)"]=$this->_configuration->getRouteAliases(Mode::API)->getMapping();
        $infos["configuration"]["getRouteAliases(PAGES)"]=$this->_configuration->getRouteAliases(Mode::PAGES)->getMapping();

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