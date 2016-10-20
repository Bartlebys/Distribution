<?php

namespace Bartleby\Core;

require_once __DIR__ . '/Context.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Configuration.php';


use Bartleby\Core\Request;
use Bartleby\Core\Configuration;

class RoutesAliases {

    /**
     * Route aliases support path templating and variables extraction.
     *
     * "/user/{userId}/comments/{numberOfComment}"
     * would normally use the class : UserCommentsWith
     *
     * but if there is an alias it will use its alias
     * e.g '/user/{userId}/comments/'=>'CommentsByUser'
     * It will use the CommentsByUser class.
     *
     * userId value will be added Context->parametersInPath['userId']=2992
     * if the string seems to be a numeric value it will be casted to an integer.
     *
     *
     * Routes Configuration by samples
     *
     *  For PAGES explicit mapping is required :
     *
     *  localhost and localhost/ will point to the Start.php page.
     *  ''=>'Start'
     *
     *  localhost/time
     *  'time'=>'Time',
     *
     * You can specify a not found mapping
     * '*' => 'NotFound'
     *
     *
     * For ENDPOINTS :
     *
     *      You can restrict to one method by prefixing the "<HTTPMethod>:"
     *      'GET:/user/{userId}/comments'=>CommentsByUser
     *
     *      You can specify a method name
     *      'POST:/user/{userId}/comments'=>array('CommentsByUser','POST_method_for_demo')
     *
             Explicit mapping like: "'nuggets'=>'Nuggets'" is not usefull
     *
     *       This is simple route alias api/v1/time will call SSTime (for any supported HTTPMethod)
     *      'time'=>'SSETime' // A server sent event sample
     *
     *
     **/

    private $_mapping = array();

    private $_class_name_suffix='';

    private $_parameters_indexes=array();

    /**
     * @var string
     */
    private $_methodName;


    /**
     * RoutesAliases constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping) {
        $this->_mapping = $mapping;
    }


    /**
     * Adds aliases or replaces existing aliases !
     * @param array $aliases
     */
    public function addAliasesToMapping(array $aliases){
        foreach ($aliases as $alias => $destination) {
            $this->_mapping[$alias]=$destination;
        }
    }


    
    /**
     * @param $path
     * @param \Bartleby\Core\Request $request
     * @param \Bartleby\Core\Configuration $configuration
     * @return Context
     */
    public function contextFromPath($path,Request $request,Configuration $configuration) {

        $httpMethod=$request->getHTTPMethod();

        // Aggregates all the variables into the Context
        // HTTP headers, QueryString, Post parameters
        // and even inject url root based based variables e.g "/user/{userId}/comments/{numberOfComment}"
        $allVariables = getallheaders();
        if (!is_array($allVariables)) {
            $allVariables=[];
        }
        $allVariables=array_merge($allVariables, $request->getData());
        // PHP 5.4 support.
        $allVariables=array_merge($allVariables,$_COOKIE);
        // Instantiate the context Descriptor
        $context = new Context($allVariables,$configuration);


        $class_name = '';
        $hasBeenAliased=false;
        if (array_key_exists($path, $this->_mapping)) {
            $class_name =$this->_extractMethodNameAndReturnDescriptor($this->_mapping[$path]);
        } else {
            $filteredPath = ltrim($path, '/');
            $filteredPath = rtrim($filteredPath, '/');
            foreach ($this->_mapping as $alias => $aliased) {

                // Method prefix support in aliases.
                $httpMethods=array('POST','GET','PUT','DELETE');
                $aliasForcedHttpMethod=NULL;
                foreach($httpMethods  as $supportedMethod){
                   if(strpos($alias, $supportedMethod.':')!==false){
                       $aliasForcedHttpMethod=$supportedMethod;
                   }
                }
                if (isset($aliasForcedHttpMethod) && $aliasForcedHttpMethod!=$httpMethod){
                    // That's not the good HTTP method
                    continue;
                }
                if (isset($aliasForcedHttpMethod) && $aliasForcedHttpMethod==$httpMethod){
                    // That's the good HTTP method
                    // Let's remove the prefix
                    $alias=str_replace($httpMethod.':','',$alias);
                }

                $this->_parameters_indexes=array();//reset
                $this->_methodName=NULL;
                if ($this->_mapsTheAlias($filteredPath, $alias, $context)) {
                    if(is_array($aliased)){
                        $class_name =$aliased[0];
                        if (count($aliased)>=1){
                            $this->_methodName=$aliased[1];
                        }
                    }else{
                        $class_name = $aliased;
                    }
                    $hasBeenAliased=true;
                    break;
                }
            }
            if ($class_name === '') {
                $pathParts = explode('/', $filteredPath);
                $i=0;
                foreach($pathParts as $part){
                    if(! in_array($i,$this->_parameters_indexes)){
                        $class_name.=ucfirst($part);
                    }
                    $i++;
                }
            }
        }
        if($hasBeenAliased==false){
            $class_name.=$this->_class_name_suffix;
        }
        $context->controllerClassName = ucfirst($class_name);
        $context->modelClassName = ucfirst($class_name) . 'CallData';
        $context->method=$this->_methodName;// If Null the Gateway we will use generic HTTP methods

        return $context;
    }

    /**
     * @param $path
     * @param $alias
     * @param $contextDescriptor Context
     * @return bool
     */
    private function _mapsTheAlias($path, $alias, $contextDescriptor) {
        if($alias=='*'){
            return true;
        }
        $stringAlias=$this->_extractMethodNameAndReturnDescriptor($alias);
        $a = ltrim($stringAlias, '/');
        $a = rtrim($a, '/');
        $pathParts = explode('/', $path);
        $aliasParts = explode('/', $a);
        if (count($aliasParts) != count($pathParts)) {
            return false;
        }
        $parameters = array();
        $i = 0;
        foreach ($aliasParts as $aliasPart) {
            $starts=substr($aliasPart,0,1);
            $ends=substr($aliasPart, -1,1);
            $isAVariable = ( $starts== "{" && $ends== "}");
            if ($isAVariable == true) {
                $variableName=substr($aliasPart,1,strlen($aliasPart)-2);
                $variableValue=$pathParts[$i];
                if(is_numeric($variableValue)){
                    $variableValue=$variableValue+0;// Convert to Scalar Ohh my God ... PHP! :)
                }
                $parameters[$variableName]=$variableValue;
                $this->_parameters_indexes[]=$i;
            } else if ($pathParts[$i] !== $aliasPart) {
                return false;
            }
            $i++;
        }
        $nbOfPInP=count($parameters);
        if($nbOfPInP==1){
            $this->_class_name_suffix='By';
        }elseif ($nbOfPInP>1){
            $this->_class_name_suffix='With';
        }
        
        // We add the path parameters to the context
        $contextDescriptor->addParameters($parameters);
        return true;
    }


    /**
     * @param $mapping
     * @return bool|mixed
     */
    private function _extractMethodNameAndReturnDescriptor($mapping){
        if(is_array($mapping)){
            if (count($mapping)>=1) {
                $this->_methodName = $mapping[1];
                return $mapping[0];
            }
        }elseif (is_string($mapping)){
            return $mapping;
        }
        // There is no explicit method Name;
        return false;
    }

    /**
     * The mapping array
     * @return array
     */
    public function getMapping(){
        return $this->_mapping;
    }
}