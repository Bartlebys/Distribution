<?php
/*

Copyright 2O16 Benoit Pereira da Silva https://pereira-da-silva.com

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/
require_once  __DIR__.'/Bundler.php';
use Bartleby\Bundler;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set ( 'UTC' );

$bundler = new Bundler();
try {
    /**
     * This method can accept arguments from a commandline or by GET
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Distribution/Bundle-sources/'
     *  Default destination is set to dirname(__DIR__).'/Distribution/Bundle.package';
     *
     * @throws \Exception
     */
    $bundler->pack();
}
catch (\Exception $e) {
    echo $e->getMessage();
}