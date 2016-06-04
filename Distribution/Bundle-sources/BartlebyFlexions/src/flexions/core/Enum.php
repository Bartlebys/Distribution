<?php

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 23/07/2015
 * Time: 17:32
 */
abstract class Enum {

    /**
     * @return array
     */
    static  function possibleValues(){
        return array();
    }

    /**
     * @return true if the value is a member of the Enum
     */
     final function isValid($value){
        $a=$this->possibleValues();
         $r=in_array($value,$a);
         return $r;
    }

}