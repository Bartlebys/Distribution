<?php

namespace Bartleby\Core;

require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/RoutesAliases.php';
require_once __DIR__ . '/Configuration.php';
require_once __DIR__ . '/IResponse.php';
require_once __DIR__ . '/JsonResponse.php';
require_once __DIR__ . '/GateKeeper.php';
require_once __DIR__ . '/Mode.php';
require_once __DIR__ . '/IAuthentified.php';

use Bartleby\Core\JsonResponse;
use Bartleby\Core\GateKeeper;
use Bartleby\Core\Mode;

// Special infos EndPoint should be blocked in production

require_once dirname(__DIR__) . '/Commons/EndPoints/Infos.php';
use Bartleby\EndPoints\Infos;
use Bartleby\EndPoints\InfosCallData;


/**
 * Class Gateway
 *
 * #1 This gateway routes the request to dedicated classes and invokes the relevant method
 * If a method is specified in the alias the method will be used
 * if not Bartleby we will call a method name per HTTP verb e.g $instance->POST($param)
 *
 * #2 It delegates the ACL to the Gatekeeper
 *
 * #3 Applies data filters (filter IN) before invocation and before response (filter OUT)
 *
 * @package Bartleby\Core
 */
class Gateway {

    /* @var IResponse $_response */
    private $_response;

    private $_filterKey;
    private $_configuration;
    /**
     * @param Configuration $configuration
     * @param string $runMode
     */
    public function __construct(Configuration $configuration,$runMode=Mode::API) {

        $request=new Request();
        $path=$request->getPath();

        $this->_configuration=$configuration;

        // Search the relevant class file.
        // We put the CallData class within the endpoint class
        // To reduce the discovery operations.
        $searchPaths = $runMode == Mode::API ? $configuration->getEndpointsSearchPaths() : $configuration->getPagesSearchPaths();
        $routeAliases=$configuration->getRouteAliases($runMode);
        $context=$routeAliases->contextFromPath($path,$request->getParameters(), $request->getHTTPMethod());
        $entitiesName=$configuration->getEntitiesName($runMode);

        $unexistingPaths=array();
        
        foreach ($searchPaths as $searchPath ){

            $path=$searchPath.$context->className.'.php';

            if(file_exists($path)){
                
                // We require this file
                require_once $path;
                // We resolve the classes.
                $classForElement='\\Bartleby\\'.$entitiesName.'\\'.$context->className;
                $classForCallData='\\Bartleby\\'.$entitiesName.'\\'.$context->callDataClassName;

                // Extract the method
                $methodName=(isset($context->method))?$context->method:$request->getHTTPMethod();

                // Extract the parameters
                $cleanParameters=$context->getCleanParameters();

                // GateKeeper
                $gateKeeper=new GateKeeper($configuration,$classForElement,$methodName);
                try{
                    $authorized=$gateKeeper->isAuthorized($cleanParameters);
                    if ($authorized===true){
                        // We instantiate the class.
                        $instance=new $classForElement($configuration);

                        // Filter IN
                        $filteredParameters=$cleanParameters;
                        $this->_filterKey=$context->className.'->'.$methodName;
                        if ($this->_configuration->hasFilterIN( $this->_filterKey)){
                            $filteredParameters=$configuration->runFilterIN($this->_filterKey,$cleanParameters);
                        }

                        // Instanciate Call Data
                        $callDataInstance=new $classForCallData($filteredParameters);
                        if ($callDataInstance instanceof IAuthentified){
                            /*@var IAuthentified */
                            $user=$gateKeeper->getCurrentUser();
                            $callDataInstance->setCurrentUser($user);
                        }


                        // Inject the information
                        //It is the special infos endpoint
                        if ($classForCallData=='Bartleby\EndPoints\InfosCallData') {
                            /*@var $callDataInstance InfosCallData*/
                            $callDataInstance->configuration=$this->_configuration;
                        }

                        // We invoke the method
                        if(method_exists($instance,$methodName)){
                            $response=$instance->{$methodName}($callDataInstance);
                            // Store the response
                            $this->_response=$response;
                            return; //  stop the execution flow.
                        }else {
                            $infos = array();
                            $infos [Configuration::INFORMATIONS_KEY] = 'Method ' . $methodName . ' is not supported';
                            $this->_response=new JsonResponse($infos, 405);
                            return; //  stop the execution flow.
                        }

                    }else{
                        if (Configuration::DEVELOPER_DEBUG_MODE==true){
                            $this->_response=new JsonResponse( array(   "cookies"=>$_COOKIE,
                                                                        "context"=>$context,
                                                                        "explanation"=>$gateKeeper->explanation
                            ),403);
                        }else {
                            $this->_response=new JsonResponse(VOID_RESPONSE,403);
                        }
                        return; //  stop the execution flow.
                    }
                }catch (\Exception $e){
                    if (Configuration::DEVELOPER_DEBUG_MODE==true){
                        $this->_response=new JsonResponse( array(  "exception"=>$e->getMessage(),
                                                                    "context"=>$context
                        ),406);
                    }else {
                        $this->_response = new JsonResponse(array("Exception" => $e->getMessage()
                        ), 406);
                    }
                    return; //  stop the execution flow.
                }
            }else{
                $unexistingPaths[]=$path;
            }
        }
        if (Configuration::DEVELOPER_DEBUG_MODE==true){
            $this->_response=new JsonResponse( array(  "message"=>"Bartleby says:\"I would prefer not to!\" - No valid route found",
                "unexistingPath"=>$unexistingPaths,
                "runMode"=>$runMode,
                "context"=>$context
            ),404);
        }else{
            $this->_response=new JsonResponse( array(  "message"=>"Bartleby says:\"I would prefer not to!\" - No valid route found"
            ),404);
        }
    }


    public function getResponse() {
        if(isset($this->_response)){
            if(isset($this->_response->data)){
                // Run Filter OUT
                if ($this->_configuration->hasFilterOUT($this->_filterKey)){
                    $this->_response->data=$this->_configuration->runFilterOUT($this->_filterKey,$this->_response->data);
                }
            }
            // Send the response
            $this->_response->send();
        }else{
            throw new \Exception("No response from gateway");
        }
    }

}