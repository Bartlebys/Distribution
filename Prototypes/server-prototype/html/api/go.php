<?php

namespace Bartleby;

require_once dirname(__DIR__) . '/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Gateway.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/JsonResponse.php';

use Bartleby\Configuration;
use Bartleby\Core\Gateway;
use Bartleby\Core\Mode;
use Bartleby\Core\JsonResponse;

$currentDirectory=__DIR__.'/';
$configuration=new Configuration($currentDirectory,BARTLEBY_ROOT_FOLDER,Mode::API);
$gateway = new Gateway($configuration);

try {
    $gateway->getResponse();
} catch (\Exception $e) {
    $status = 500;
    $header = 'HTTP/1.1 ' . $status . ' ' . JsonResponse::getRequestStatus($status);
    header($header);
    echo json_encode(Array('error' => $e->getMessage()));
}