<?php

namespace Bartleby\Mongo;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoConfiguration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Controller.php';

use Bartleby\Core\Controller;
use Bartleby\Core\IPersistentController;
use \MongoClient;
use \MongoCursorException;
use \MongoDB;
use \Bartleby\Core\JsonResponse;

class MongoController extends Controller {
}