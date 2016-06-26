<?php

namespace Bartleby\Core;

require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/RoutesAliases.php';
require_once __DIR__ . '/IResponse.php';
require_once __DIR__ . '/JsonResponse.php';
require_once __DIR__ . '/GateKeeper.php';
require_once __DIR__ . '/Mode.php';
require_once __DIR__ . '/IAuthentified.php';


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
     * @var string
     */
    private $_filterKey;

    /**
     * @var Configuration
     */
    private $_configuration;

    /**
     * @param Configuration $configuration
     * @param string $runMode
     */
    public function __construct(Configuration $configuration, $runMode = Mode::API) {

        $request = new Request();
        $path = $request->getPath();

        $this->_configuration = $configuration;
        
        // Search the relevant class file.
        // We put the CallData class within the endpoint class
        // To reduce the discovery operations.
        $searchPaths = $runMode == Mode::API ? $configuration->getEndpointsSearchPaths() : $configuration->getPagesSearchPaths();
        $routeAliases = $configuration->getRouteAliases($runMode);
        $context = $routeAliases->contextFromPath($path, $request->getParameters(), $request->getHTTPMethod());
        $entitiesName = $configuration->getEntitiesName($runMode);

        $unexistingPaths = array();

        // Try first to use a fixed Path 
        // So You can Overload the standard path and define a fixed One
        // To to so you can call `definePath in Configuration for example :
        // $this->definePath("ClassName", $this->_bartlebyRootDirectory . 'Commons/Overloads/EndPoints/ClassName.php');`
        $filePath = $configuration->getFixedPathForClassName($context->className);

        if ($filePath == "") {
            // There are no fixed paths.
            // Let's try to resolve using search Paths
            foreach ($searchPaths as $searchPath) {
                $possiblePath = $searchPath . $context->className . '.php';
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
                    "context" => $context
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
                    $classForElement = '\\Bartleby\\' . $entitiesName . '\\Overloads\\' . $context->className;
                    // We donnot use the overloaded class.
                    $classForCallData = '\\Bartleby\\' . $entitiesName . '\\' . $context->callDataClassName;

                }else{
                    // We resolve the classes.
                    $classForElement = '\\Bartleby\\' . $entitiesName . '\\' . $context->className;
                    $classForCallData = '\\Bartleby\\' . $entitiesName . '\\' . $context->callDataClassName;

                }

                // Extract the method
                $methodName = (isset($context->method)) ? $context->method : $request->getHTTPMethod();

                // Extract the parameters
                $cleanParameters = $context->getCleanParameters();

                // Instantiate the GateKeeper
                $gateKeeper = new GateKeeper($configuration, $classForElement, $methodName);

                if ($this->_configuration->DISABLE_ACL() === true) {
                    // We authorize any operation
                    $authorized = true;
                }else{
                    $authorized = $gateKeeper->isAuthorized($cleanParameters);
                }

                if ($authorized === true) {
                    // We instantiate the class.
                    $instance = new $classForElement($configuration);

                    // Filter IN
                    $filteredParameters = $cleanParameters;
                    $this->_filterKey = $context->className . '->' . $methodName;
                    if ($this->_configuration->hasFilterIN($this->_filterKey)) {
                        $filteredParameters = $configuration->runFilterIN($this->_filterKey, $cleanParameters);
                    }

                    // Instanciate Call Data
                    $callDataInstance = new $classForCallData($filteredParameters);
                    if ($callDataInstance instanceof IAuthentified) {
                        /*@var IAuthentified */
                        $user = $gateKeeper->getCurrentUser();
                        $callDataInstance->setCurrentUser($user);
                    }

                    // Inject the information
                    //It is the special infos endpoint
                    if ($classForCallData == 'Bartleby\EndPoints\InfosCallData') {
                        /*@var $callDataInstance InfosCallData*/
                        $callDataInstance->configuration = $this->_configuration;
                    }

                    // We invoke the method
                    if (method_exists($instance, $methodName)) {
                        $response = $instance->{$methodName}($callDataInstance);
                        // Store the response
                        $this->_response = $response;
                        return; //  stop the execution flow.
                    } else {
                        $infos = array();
                        $infos [$configuration::INFORMATIONS_KEY] = 'Method ' . $methodName . ' is not supported';
                        $this->_response = new JsonResponse($infos, 405);
                        return; //  stop the execution flow.
                    }

                } else {
                    if ($this->_configuration->DEVELOPER_DEBUG_MODE() == true) {
                        $this->_response = new JsonResponse(array("cookies" => $_COOKIE,
                            "context" => $context,
                            "explanation" => $gateKeeper->explanation
                        ), 403);
                    } else {
                        $this->_response = new JsonResponse(VOID_RESPONSE, 403);
                    }
                    return; //  stop the execution flow.
                }

            } catch (\Exception $e) {
                if ($this->_configuration->DEVELOPER_DEBUG_MODE() == true) {
                    $this->_response = new JsonResponse(array("exception" => $e->getMessage(),
                        "context" => $context
                    ), 406);
                } else {
                    $this->_response = new JsonResponse(array("Exception" => $e->getMessage()
                    ), 406);
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
                if ($this->_configuration->hasFilterOUT($this->_filterKey)) {
                    $this->_response->data = $this->_configuration->runFilterOUT($this->_filterKey, $this->_response->data);
                }
            }
            // Send the response
            $this->_response->send();
        } else {
            throw new \Exception("No response from gateway");
        }
    }

}