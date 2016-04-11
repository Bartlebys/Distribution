<?php

require_once  __DIR__.'/Bundler.php';
use Bartleby\Bundler;

$bundler = new Bundler();
try {
    /**
     * This method can accept arguments from a commandline or by GET
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Bundle.package.zip'
     *  Default destination is set to dirname(__DIR__).'/ExpandedBundle/'
     *
     * @throws \Exception
     */
    $bundler->unPack();
}
catch (\Exception $e) {
    echo $e->getMessage();
}