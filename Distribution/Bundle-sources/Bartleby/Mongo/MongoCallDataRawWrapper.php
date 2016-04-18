<?php


namespace Bartleby\mongo;

require_once dirname(__DIR__) . '/Core/CallDataRawWrapper.php';

use Bartleby\Core\CallDataRawWrapper;

class MongoCallDataRawWrapper extends CallDataRawWrapper {

    // Sort values must be casted to numeric values
    // So we propose some facilities to transform

    function getCastedDictionaryForKey($key){
       return $this->castNumericValues($this->getValueForKey($key));
    }

    function castNumericValues($values){
        if(!is_array($values)){
            return $values;
        }
        foreach ($values as $key => $value){
            if(is_array($value)){
                $this->castNumericValues($value);
            }else{
                if(is_numeric($value)){
                    $values[$key]=0+$value;
                }
            }
            return $values;
        }
}

}