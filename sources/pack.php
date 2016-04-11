<?php


require_once  __DIR__.'/Bundler.php';
use Bartleby\Bundler;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set ( 'UTC' );

$bundler = new Bundler();
try {
    /**
     * This method can accept arguments from a commandline or by GET
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Bundled/'
     *  Default destination is set to dirname(__DIR__).'/Bundle.package';
     *
     * @throws \Exception
     */
    $bundler->pack();
}
catch (\Exception $e) {
    echo $e->getMessage();
}