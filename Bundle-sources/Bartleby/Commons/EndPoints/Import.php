<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 08/07/2016
 * Time: 09:31
 */

namespace Bartleby\Commons\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';

use Bartleby\Core\JsonResponse;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;

final class ImportCallData extends MongoCallDataRawWrapper {

}

class Import extends MongoEndPoint{

}