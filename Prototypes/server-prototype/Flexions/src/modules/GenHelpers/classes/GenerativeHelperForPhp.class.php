<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 10:37
 */
require_once 'GenerativeHelper.php';

class GenerativeHelperForPhp extends GenerativeHelper {

    static function defaultHeader(Flexed $f, $d) {
        $header = "
/**
* Generated by BARTLEBY'S Flexions for $f->author on ?
* https://github.com/Bartlebys
*
* DO NOT MODIFY THIS FILE YOUR MODIFICATIONS WOULD BE ERASED ON NEXT GENERATION!
*
* Copyright (c) 2016  $f->company  All rights reserved.
*/
";
        return $header;

    }


}