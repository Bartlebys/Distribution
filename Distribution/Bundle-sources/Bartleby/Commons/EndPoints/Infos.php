<?php

namespace Bartleby\EndPoints;

require_once dirname(dirname(__DIR__)) . '/Mongo/MongoEndPoint.php';

use Bartleby\Core\Configuration;
use Bartleby\Core\CallDataRawWrapper;
use Bartleby\Core\JsonResponse;
use Bartleby\Core\Mode;
use Bartleby\Mongo\MongoEndPoint;


final class InfosCallData extends CallDataRawWrapper{

}

final class Infos extends MongoEndPoint {

    function GET(InfosCallData $parameters){
        $infos=array();

        // Configuration

        $infos["version_of_Bartleby"]=Configuration::BARTLEBY_VERSION.'.'.Configuration::BARTLEBY_RELEASE;
        $infos["configuration"]=array();
        $infos["configuration"]["get_STAGE"]=$this->_configuration->get_STAGE();
        $infos["configuration"]["get_VERSION"]=$this->_configuration->get_VERSION();
        $infos["configuration"]["getBartlebyRootDirectory"]=$this->_configuration->getBartlebyRootDirectory();
        $infos["configuration"]["getEndpointsSearchPaths"]=$this->_configuration->getEndpointsSearchPaths();
        $infos["configuration"]["getPagesSearchPaths"]=$this->_configuration->getPagesSearchPaths();
        $infos["configuration"]["getPermissionsRules"]=$this->_configuration->getPermissionsRules();
        $infos["configuration"]["getRouteAliases(API)"]=$this->_configuration->getRouteAliases(Mode::API)->getMapping();
        $infos["configuration"]["getRouteAliases(PAGES)"]=$this->_configuration->getRouteAliases(Mode::PAGES)->getMapping();
        return new JsonResponse($infos, 200);
    }


}