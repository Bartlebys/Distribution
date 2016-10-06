<?php

namespace Bartleby;

require_once dirname(__DIR__) . '/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Gateway.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Response.php';

use Bartleby\Configuration;
use Bartleby\Core\Gateway;
use Bartleby\Core\Mode;
use bartleby\Core\Response;

$currentDirectory=__DIR__.'/';
$configuration=new Configuration($currentDirectory,BARTLEBY_ROOT_FOLDER);;
$gateway = new Gateway($configuration,Mode::PAGES);

try {
    $gateway->getResponse();
} catch (\Exception $e) {
    $status = 500;
    $header = 'HTTP/1.1 ' . $status . ' ' . Response::getRequestStatus($status);
    header($header);
    echo json_encode(Array('error' => $e->getMessage()));
}
