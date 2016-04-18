<?php

namespace Bartleby\Mongo;

require_once __DIR__ . '/MongoController.php';
require_once dirname(__DIR__) . '/Core/CallData.php';
require_once dirname(__DIR__) . '/Core/HTMLResponse.php';

use \MongoClient;


class MongoPage extends MongoController {

    public $POST = "POST";
    public $GET = "GET";

}