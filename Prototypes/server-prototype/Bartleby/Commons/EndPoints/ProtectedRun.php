<?php
namespace Bartleby\Commons\EndPoints;
namespace Bartleby\EndPoints;

require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoEndPoint.php';
require_once BARTLEBY_ROOT_FOLDER . 'Mongo/MongoCallDataRawWrapper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/HTMLResponse.php';

use Bartleby\Commons\Pages\Bootstrap3XPage;use Bartleby\Core\Configuration;
use Bartleby\Core\HTMLResponse;
use Bartleby\Core\JsonResponse;
use Bartleby\Core\Mode;
use Bartleby\mongo\MongoCallDataRawWrapper;
use Bartleby\Mongo\MongoEndPoint;
use Bartleby\Core\KeyPath;


final class ProtectedRunCallData extends MongoCallDataRawWrapper {

    // Set a key if you want only that key.
    const fileToRun = "fileToRun";

    const  useText = "useText";
}

/**
 * Allow to run a script located in the protected Section
 * eg: http://localhost/api/v1/run?fileToRun=echo.php
 * http://localhost/api/v1/run?fileToRun=maintenance_ephemeralRemover.php
 */
final class ProtectedRun extends MongoEndPoint {

    function GET(){
        /* @var ProtectedRunCallData */
        $parameters=$this->getModel();
        $fileToRun=$parameters->getValueForKey(ProtectedRunCallData::fileToRun);
        $useText=$parameters->getValueForKey(ProtectedRunCallData::useText);
        if(!isset($useText)){
            $useText=false;// We use JSON
        }

        $result=null;
        $filePath=BARTLEBY_PUBLIC_FOLDER.'Protected/'.$fileToRun;
        if (file_exists($filePath)){
            // ( ! ) Template execution
            ob_start ();@include $filePath;$result = ob_get_clean ();
        }else{
            throw  new \Exception("Unexisting file ".$filePath);
        }

        if (!isset($result)){
            $result="Nothing";
        }
        if ($useText && strtolower($useText) == "true") {
            $response=new HTMLResponse();
            $response->document=$result;
            $response->statusCode=200;
            return $response;
        }else{
            $response=json_encode(["message"=>$result]);
            // (!) End of template execution
            return new JsonResponse($response, 200);
        }
    }


}