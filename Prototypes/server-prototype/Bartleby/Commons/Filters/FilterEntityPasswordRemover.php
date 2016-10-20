<?php

namespace Bartleby\Filters;

require_once dirname(dirname(__DIR__)) . '/Core/IFilter.php';
require_once dirname(dirname(__DIR__)) . '/Core/KeyPath.php';

use Bartleby\Core\IFilter;
use Bartleby\Core\KeyPath;

class FilterEntityPasswordRemover implements IFilter {

    /**
     * You should set the relevant key if necessary
     * @var string
     */
    var $passwordKeyPath="password";

    function filterData($data){
        if (isset($data)){
            //KeyPath::removeKeyPathByReference($data,$this->passwordKeyPath);
            KeyPath::setValueByReferenceForKeyPath($data,$this->passwordKeyPath,"");
        }
        return $data;
    }


}