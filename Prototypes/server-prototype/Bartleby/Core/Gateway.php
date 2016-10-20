<?php

namespace Bartleby\Core;

require_once BARTLEBY_ROOT_FOLDER . 'Core/Configuration.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Request.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/RoutesAliases.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/IResponse.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/JsonResponse.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/GateKeeper.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Mode.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/IAuthentified.php';
require_once BARTLEBY_ROOT_FOLDER . 'Core/Context.php';


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

    /**
     * @var \Bartleby\Core\IResponse
     */
    private $_response;

    /**
     * The key used to determine the presence of filters IN/OUT
     * @var string
     */
    private $_filterKey="";


    /* @var Context */
    private $_context;

    /* @var Configuration */
    private $_configuration;

    /**
     * Gateway constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration) {

        $request = new Request();
        $path = $request->getPath();

        $this->_configuration = $configuration;
        $runMode=$this->_configuration->runMode;

        // Search the relevant class file.
        // We put the CallData class within the endpoint class
        // To reduce the discovery operations.
        $searchPaths = ($runMode == Mode::API ? $this->_configuration->getEndpointsSearchPaths() : $this->_configuration->getPagesSearchPaths());
        $routeAliases = $this->_configuration->getRouteAliases($runMode);
        
        // Extract the Context.
        $this->_context = $routeAliases->contextFromPath($path, $request,$this->_configuration);

        $entitiesName = $this->_configuration->getEntitiesName($runMode);
        $unexistingPaths = array();

        // Try first to use a fixed Path 
        // So You can Overload the standard path and define a fixed One
        // To to so you can call `definePath in Configuration for example :
        // $this->definePath("ClassName", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/ClassName.php');`
        $filePath = $this->_configuration->getFixedPathForClassName($this->_context->controllerClassName);

        if ($filePath == "") {
            // There are no fixed paths.
            // Let's try to resolve using search Paths
            foreach ($searchPaths as $searchPath) {
                $possiblePath = $searchPath . $this->_context->controllerClassName . '.php';
                if (file_exists($possiblePath)) {
                    $filePath = $possiblePath;
                } else {
                    $unexistingPaths[] = $possiblePath;
                }
            }
        }

        if ($filePath == "") {
            // We haven't found any valid File Path
            if ($this->_configuration->DEVELOPER_DEBUG_MODE() == true) {
                $this->_response = new JsonResponse(array("message" => "Bartleby says:\"I would prefer not to!\" - No valid route found",
                    "unexistingPath" => $unexistingPaths,
                    "runMode" => $runMode,
                    "context" => $this->_context
                ), 404);
            } else {
                $this->_response = new JsonResponse(array("message" => "Bartleby says:\"I would prefer not to!\" - No valid route found"
                ), 404);
            }
            return; //  stop the execution flow.
        } else {
            // That's the "normal case"
            // The related file has been found.

            try {

                // Let's require this file
                require_once $filePath;

                if (strpos($filePath,'/Overloads/')!==false){
                    // We use the overloaded class
                    $classForElement = '\\Bartleby\\' . $entitiesName . '\\Overloads\\' . $this->_context->controllerClassName;
                    // We donnot use the overloaded class.
                    $classForCallData = '\\Bartleby\\' . $entitiesName . '\\' . $this->_context->modelClassName;

                }else{
                    // We resolve the classes.
                    $classForElement = '\\Bartleby\\' . $entitiesName . '\\' . $this->_context->controllerClassName;
                    $classForCallData = '\\Bartleby\\' . $entitiesName . '\\' . $this->_context->modelClassName;

                }

                // Extract the method if not defined use the HTTP method as a method name.
                $methodName = (isset($this->_context->method)) ? $this->_context->method : $request->getHTTPMethod();


                // Instantiate the GateKeeper
                $gateKeeper = new GateKeeper($this->_context, $classForElement, $methodName);

                if ($this->_configuration->DISABLE_ACL() === true) {
                    // We authorize any operation
                    $authorized = true;
                }else{
                    $authorized = $gateKeeper->isAuthorized();
                }

                if ($authorized === true) {

                    // Filter IN
                    $filteredParameters = $this->_context->getVariables();
                    $this->_filterKey = $this->_context->controllerClassName . '->' . $methodName;
                    if ($this->_configuration->hasFilterIN($this->_filterKey) && $this->_configuration->DISABLE_DATA_FILTERS() == false) {
                        $filteredParameters = $this->_configuration->runFilterIN($this->_filterKey, $this->_context->getVariables());
                    }

                    // Instanciate Call Data
                    $callDataInstance = new $classForCallData($filteredParameters);
         
                    // We instantiate the class.
                    $instance = new $classForElement($callDataInstance,$this->_context);

                    // We invoke the method
                    if (method_exists($instance, $methodName)) {
                        $response = $instance->{$methodName}();
                        // Store the response
                        $this->_response = $response;
                        return; //  stop the execution flow.
                    } else {
                        $infos = array();
                        $infos [Configuration::INFORMATIONS_KEY] = 'Method ' . $methodName . ' is not supported';
                        $this->_response = new JsonResponse($infos, 405);
                        return; //  stop the execution flow.
                    }

                } else {
                    if ($this->_configuration->DEVELOPER_DEBUG_MODE() == true) {
                        $this->_response = new JsonResponse([   "context" => $this->_context ], 403);
                    } else {
                        $this->_response = new JsonResponse(VOID_RESPONSE, 403);
                    }
                    return; //  stop the execution flow.
                }

            } catch (\Exception $e) {
                if ($this->_configuration->DEVELOPER_DEBUG_MODE() == true) {
                    $this->_response = new JsonResponse([   "exception" => $e->getMessage(),
                                                            "context" => $this->_context ], 406);
                } else {
                    $this->_response = new JsonResponse([ "Exception" => $e->getMessage()], 406);
                }
                return; //  stop the execution flow.
            }
        }
    }

    /**
     * Sends the response or throws an Exception.
     * @throws \Exception
     */
    public function getResponse() {
        if (isset($this->_response)) {
            if ($this->_response instanceof IHTTPResponse){
                if ($this->_response->getStatusCode() <= 0) {
                    throw new \Exception("Inconsistent HTTPStatus code");
                }
            }
            if (isset($this->_response->data)) {
                // Run Filter OUT
                if ($this->_configuration->hasFilterOUT($this->_filterKey) && $this->_configuration->DISABLE_DATA_FILTERS() == false) {
                    $this->_response->data = $this->_configuration->runFilterOUT($this->_filterKey, $this->_response->data);
                }
            }
            // Determine if we should use prettyprint
            $this->_response->usePrettyPrint($this->_context->usePrettyPrint());
            // Send the response
            $this->_response->send();
        } else {
            throw new \Exception("No response from gateway");
        }
    }

}