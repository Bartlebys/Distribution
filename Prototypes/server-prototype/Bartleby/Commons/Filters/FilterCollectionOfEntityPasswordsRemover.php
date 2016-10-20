<?php

namespace Bartleby\Filters;

require_once dirname(dirname(__DIR__)) . '/Core/IFilter.php';
require_once dirname(dirname(__DIR__)). '/Core/KeyPath.php';

use Bartleby\Core\IFilter;
use Bartleby\Core\KeyPath;


class FilterCollectionOfEntityPasswordsRemover implements IFilter {

    /**
     * You should set the relevant keyPath if necessary
     * @var string
     */
    var $iterableCollectionKeyPath=NULL;

    /**
     * You should set the relevant keyPath if necessary
     * @var string
     */
    var $passwordKeyPath="password";


    function filterData($data){
        if (isset($data)){
            if(isset($this->iterableCollectionKeyPath)){
                $collection=KeyPath::valueForKeyPath($data,$this->iterableCollectionKeyPath);
            }else{
                $collection=$data;
            }
            if (isset($collection) && is_array($collection)){
                foreach ($collection as &$entity) {
                    KeyPath::setValueByReferenceForKeyPath($entity,$this->passwordKeyPath,"");
                }
                // Return the filtered collection
                return $collection;
            }
        }
        return $data;
    }


}