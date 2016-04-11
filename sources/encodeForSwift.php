<?php
require_once  __DIR__.'/Bundler.php';
use Bartleby\Bundler;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set ( 'UTC' );

$bundler = new Bundler();
try {
    /**
     * This method can accept arguments from a commandline or by GET
     * "source" the file will be encoded to stored in swift string
     * @throws \Exception
     */
    $bundler->encodeForSwift();
}
catch (\Exception $e) {
    echo $e->getMessage();
}