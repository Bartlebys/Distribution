<?php

namespace Bartleby\EndPoints;

include_once dirname(dirname(__FILE__)).'/BartlebySyncConfiguration.php';

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_SYNC_ROOT_PATH.'Core/CommandInterpreter.php';
require_once BARTLEBY_SYNC_ROOT_PATH.'Core/IOManager.php';

use Bartleby\Core\CallData;
use Bartleby\Mongo\MongoEndPoint;
use BartlebySync\Core\CommandInterpreter;
use BartlebySync\Core\IOManager;
// We donnot use CallDataRawWrapper but a callData
// We reserve CallDataRawWrapper to generated code
abstract class BartlebySyncAbstractEndPointCallData extends CallData{
    /**
     * The creative key
     * @var string*/
    public $key=NULL;

}

abstract class BartlebySyncAbstractEndPoint extends MongoEndPoint {

    /**
     * The command interpreter
     *
     * @var CommandInterpreter
     */
    protected $interpreter = NULL;

    /**
     *
     * @var IOManager
     */
    protected $ioManager = NULL;

    /**
     * A lazy loading command interpreter
     * with its associated file manager
     *
     * @return CommandInterpreter the interpreter
     */
    protected function getInterpreter() {
        if (! $this->interpreter) {
            $this->interpreter = new CommandInterpreter ();
            $this->interpreter->setIOManager ( $this->getIoManager () );
        }
        return $this->interpreter;
    }

    /**
     *
     * @return IOManager the current IO manager
     */
    protected function getIoManager() {
        if (! $this->ioManager) {
            $className = PERSISTENCY_CLASSNAME;
            $this->ioManager = new $className ();
        }
        return $this->ioManager;
    }

    /**
     * Casts to boolean
     * @param mixed
     * @return bool
     */
    protected  function _castToBoolean($value){
        if (is_string($value)){
            $lcvalue=strtolower($value);
            if ($lcvalue ==='false'||$lcvalue ==='no'||$lcvalue ==='0'){
                return false;
            }else{
                return true;
            }
        }
        if (is_numeric($value)){
            $nvalue=(int)$value;
            if ($nvalue<=0){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

}