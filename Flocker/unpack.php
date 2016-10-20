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
require_once  __DIR__.'/Flocker.php';
use Bartleby\Flocker;

$flocker = new Flocker();
try {
    /**
     * This method can accept arguments from a commandline or by GET
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Bundle.package.zip'
     *  Default destination is set to dirname(__DIR__).'/ExpandedBundle/'
     *
     * @throws \Exception
     */
    $flocker->unPack();
}
catch (\Exception $e) {
    echo $e->getMessage();
}