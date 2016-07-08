<?php

namespace Bartleby\Mongo;

require_once BARTLEBY_ROOT_FOLDER . 'Core/CallData.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/CallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/JsonResponse.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoController.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';


class MongoEndPoint extends MongoController {

    public $POST = "POST";
    public $GET = "GET";
    public $PUT = "PUT";
    public $DELETE = "DELETE";

}