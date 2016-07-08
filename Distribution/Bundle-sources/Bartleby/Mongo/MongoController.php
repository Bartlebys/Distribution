<?php

namespace Bartleby\Mongo;

use Bartleby\Core\Controller;
use Bartleby\Core\IPersistentController;
use \MongoClient;
use \MongoCursorException;
use \MongoDB;
use \Bartleby\Core\JsonResponse;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Controller.php';

class MongoController extends Controller {
}