<?php

namespace Bartleby\Core;


class ContextDescriptor {

    /**
     * @var array
     */
    public $parameters=array();

    /**
     * @var string
     */
    public $className='';
    /**
     * @var string
     */
    public $callDataClassName='';

    /**
     *
     * @var string
     */
    public $method;


    public function getCleanParameters(){
        return $this->_cleanInputs($this->parameters);
    }

    private function _cleanInputs($data) {
        $clean_input = Array ();
        if (is_array ( $data )) {
            foreach ( $data as $k => $v ) {
                $clean_input [$k] = $this->_cleanInputs ( $v );
            }
        } else {
            $clean_input = trim ( strip_tags ( $data ) );
        }
        return $clean_input;
    }
}