<?php

namespace Bartleby\Filters;

require_once dirname(dirname(__DIR__)) . '/Core/IFilter.php';

use Closure;
use Bartleby\Core\IFilter;


/**
 * Class FilterHookByClosure
 *  Allows to filter data using a closure.
 * @package Bartleby\Filters
 */
class FilterHookByClosure implements IFilter {

    /* @var $closure Closure */
    var $closure;

    function filterData($data){
        if (isset($data)){
            if(isset($this->closure) && $this->_is_closure($this->closure)){
                return  $this->closure->__invoke($data);
            }else{
                throw new \Exception("Closure expected in FilterHookByClosure");
            }
        }
        return $data;
    }

    private function _is_closure($t) {
        return is_object($t) && ($t instanceof Closure);
    }


}