<?php

namespace Bartleby\Core;

require_once __DIR__ . '/ContextDescriptor.php';


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
     * userId value will be added ContextDescriptor->parametersInPath['userId']=2992
     * if the string seems to be a numeric value it will be casted to an integer.
     *
     *
     * Routes Configuration by samples
     *
     *  For PAGES explicit mapping is required :
     *
     *  yd.local and yd.local/ will point to the Start.php page.
     *  ''=>'Start'
     *
     *  yd.local/time
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

    public function contextFromPath($path,array $parameters,$httpMethod) {
        $contextDescriptor = new ContextDescriptor();
        $contextDescriptor->parameters=$parameters;
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
                if ($this->_mapsTheAlias($filteredPath, $alias, $contextDescriptor)) {
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
        $contextDescriptor->className = ucfirst($class_name);

        $contextDescriptor->callDataClassName = ucfirst($class_name) . 'CallData';
        $contextDescriptor->method=$this->_methodName;// If Null the Gateway we will use generic HTTP methods
        return $contextDescriptor;
    }


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
        $contextDescriptor->parameters=array_merge($contextDescriptor->parameters,$parameters);
        return true;
    }


    private function _extractMethodNameAndReturnDescriptor($mapping){
        if(is_array($mapping)){
            if (count($mapping)>=1) {
                $this->_methodName = $mapping[1];
                return $mapping[0];
            }else{
                // There is no explicit method Name;
                return false;
            }
        }elseif (is_string($mapping)){
            return $mapping;
        }
    }

    /**
     * The mapping array
     * @return array
     */
    public function getMapping(){
        return $this->_mapping;
    }
}