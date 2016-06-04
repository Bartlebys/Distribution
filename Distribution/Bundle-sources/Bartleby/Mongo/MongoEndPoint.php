<?php

namespace Bartleby\Mongo;

require_once dirname(__DIR__) . '/Core/CallData.php';
require_once dirname(__DIR__) . '/Core/CallDataRawWrapper.php';
require_once dirname(__DIR__) . '/Core/JsonResponse.php';
require_once dirname(__DIR__) . '/Core/Configuration.php';
require_once __DIR__ . '/MongoController.php';
require_once __DIR__ . '/MongoCallDataRawWrapper.php';


class MongoEndPoint extends MongoController {

    public $POST = "POST";
    public $GET = "GET";
    public $PUT = "PUT";
    public $DELETE = "DELETE";

}