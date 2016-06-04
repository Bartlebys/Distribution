<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'xOS/operations/';
}

// Exclusion
$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}


$dataSpaceSyntagm='inDataSpace';

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    echoIndent(cr(),0);
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
}else{
    echoIndent(cr(),0);
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
    echoIndentCR("import BartlebyKit",0);
}
?>
#endif
<?php

//////////////////////////////
/// START OF PARAMETER MODEL
/////////////////////////////


/*@var array */
$GLOBAL_GENERATED_ACTIONS[]=$d->class.'Parameters';

// We generate the parameter class if there is a least one parameter.
if ($d->containsParametersOutOfPath()) {
    echoIndentCR('@objc('.$d->class .'Parameters'.') public class ' . $d->class . 'Parameters : ' . GenerativeHelperForSwift::getBaseClass($f, $d) . ' {', 0);

    // Universal type support
    echoIndentCR('');
    echoIndentCR('// Universal type support',1);
    echoIndentCR('override public class func typeName() -> String {',1);
        echoIndentCR(' return "'.$d->class . 'Parameters"',2);
    echoIndentCR('}',1);


    while ($d->iterateOnParameters() === true) {
        $parameter = $d->getParameter();
        $name = $parameter->name;

        if (!$d->parameterIsInPath($name)) {
            echoIndentCR('// ' . $parameter->description . cr(), 1);
            if ($d->firstParameter()) {
            }
            if ($parameter->type == FlexionsTypes::ENUM) {
                $enumTypeName = $d->name . ucfirst($name);
                echoIndentCR('public enum ' . $enumTypeName . ' : ' . ucfirst($parameter->instanceOf) . '{', 1);
                foreach ($parameter->enumerations as $element) {
                    if ($parameter->instanceOf == FlexionsTypes::STRING) {
                        echoIndentCR('case ' . ucfirst($element) . ' = "' . $element . '"', 2);
                    } else {
                        echoIndentCR('case ' . ucfirst($element) . ' = ' . $element . '', 2);
                    }
                }
                echoIndentCR('}', 1);
                echoIndentCR('public var ' . $name . ':' . $enumTypeName . '?', 1);
            } else if ($parameter->type == FlexionsTypes::COLLECTION) {
                echoIndentCR('public var ' . $name . ':[' . ucfirst($parameter->instanceOf) . ']?', 1);
            } else if ($parameter->type == FlexionsTypes::OBJECT) {
                echoIndentCR('public var ' . $name . ':' . ucfirst($parameter->instanceOf) . '?', 1);
            } else {
                $nativeType = FlexionsSwiftLang::nativeTypeFor($parameter->type);
                if (strpos($nativeType, FlexionsTypes::NOT_SUPPORTED) === false) {
                    echoIndentCR('public var ' . $name . ':' . $nativeType . '?', 1);
                } else {
                    echoIndentCR('public var ' . $name . ':Not_Supported = Not_Supported//' . ucfirst($parameter->type), 1);
                }
            }
        }
    }
    echo('
    required public init(){
        super.init()
    }
');
    $blockRepresentation = $d;
    // Mappable
    include FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/Mappable.swift.block.php';
    // NSSecureCoding?
    if ($modelsShouldConformToNSCoding) {
        include FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
    }
    echoIndentCR("}",0);
}

?>



<?php
///////////////////////////////////
/// START OF END POINT EXEC CLASS
//////////////////////////////////
?>
@objc(<?php echo $d->class; ?>) public class <?php echo $d->class; ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d) ?>{

    // Universal type support
    override public class func typeName() -> String {
           return "<?php echo $d->class; ?>"
    }


    public static func execute(<?php
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$pathVCounter=0;
$hasdID= in_array('spaceUID',$pathVariables);
if (!$hasdID){
    echoIndentCR('fromDataSpace spaceUID:String,',$pathVCounter>0);
}

if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        if ($pathVariable=='spaceUID'){
            $hasdID=true;
        }
        // Suspended
        echoIndentCR($pathVariable.':String,',6);
        $pathVCounter++;
    }
}


?>
<?php

$successP = $d->getSuccessResponse();
$successTypeString = '';
if ($successP->type == FlexionsTypes::COLLECTION) {
    $successTypeString = '['.$successP->instanceOf.']';
} else if ($successP->type == FlexionsTypes::OBJECT) {
    $successTypeString = ucfirst($successP->instanceOf);
} else if ($successP->type == FlexionsTypes::DICTIONARY) {
    $successTypeString = 'Dictionary<String, AnyObject>';
}else {
    $nativeType = FlexionsSwiftLang::nativeTypeFor($successP->type);
    if($nativeType==FlexionsTypes::NOT_SUPPORTED){
        $successTypeString='';
    }else{
        $successTypeString=$nativeType;
    }
}

$resultSuccessIsACollection=($successP->type == FlexionsTypes::COLLECTION);
if($resultSuccessIsACollection){
    $successParameterName= Pluralization::pluralize(lcfirst($h->ucFirstRemovePrefixFromString($successP->instanceOf)));
}else{
    if($successP->isGeneratedType==true){
        $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successTypeString));
    }else{
        $successParameterName='result';
    }
}


$resultSuccessTypeString=$successTypeString!=''?$successParameterName.':'.$successTypeString:'';
if ($d->containsParametersOutOfPath()) {
    echoIndentCR('parameters:' . $d->class . 'Parameters,' , 6);
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', 6);
} else {
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', 6);
}

// We want to inject the path variable
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path= (strpos($d->path,'/')!==false) ? substr($d->path,1):$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}
echoIndentCR('failureHandler failure:(context:JHTTPResponse)->()){', 6);
echoIndentCR('');
    $parametersString='';
    if ($d->containsParametersOutOfPath()) {
        $parametersString='[';
        while ($d->iterateOnParameters() === true) {
            $parameter = $d->getParameter();
            $name = $parameter->name;
            $parametersString.='"'.$name.'":parameters.'.$name;
            if($parameter->type==FlexionsTypes::ENUM) {
                $parametersString.='?.rawValue';
            }
            if (!$d->lastParameter()){
                $parametersString.=',';
            }
        }
        $parametersString.=']';
    }
// We need to parse the responses.

$status2XXHasBeenDefined=false;
$successMicroBlock=NULL;
ksort($d->responses); // We sort the key by codes
foreach ($d->responses as $rank=>$responsePropertyRepresentation ) {
    /* @var  $responsePropertyRepresentation PropertyRepresentation */
    $code = $responsePropertyRepresentation->name;
    if (strpos($code, '2') === 0) {
        // THERE SHOULD HAVE ONE 2XX HTTP CODE per endpoint
        // THE OTHER WILL CURRENTLY BE IGNORED
        // DEFINE AT LEAST ONE IF YOU WANT TO DETERMINE THE RESPONSE MODEL
        // ELSE IT WILL BE INFERRED
        // YOU CAN CHECK $successTypeString TO UNDERSTAND THE INFERENCE MECANISM
        if ($status2XXHasBeenDefined == false) {
            $status2XXHasBeenDefined = true;

            if($responsePropertyRepresentation->isGeneratedType) {
                // We wanna cast the result if there is one specified
                $successMicroBlock = stringIndent(
''.(($resultSuccessIsACollection)?
    'if let instance = Mapper <' . $successP->instanceOf . '>().mapArray(result.value){
    '
    :'if let instance = Mapper <' . $successTypeString . '>().map(result.value){
    ')
.'
    success(' . $successParameterName . ': instance)
  }else{
   let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
        context: context,
        title: NSLocalizedString("Deserialization issue",
            comment: "Deserialization issue"),
        body:"(result.value)",
        trigger:{ (selectedIndex) -> () in
    })
   reactions.append(failureReaction)
   failure(context:context)
}',5);
            }
        }
    }
}

if( !isset($successMicroBlock)){

    if($successTypeString==''){
        // there is no return type
        $successMicroBlock =stringIndentCR('success()',4);
    }else{
        $successMicroBlock =stringIndent(
            '
if let r=result.value as? ' . $successTypeString . '{

    success(' . $successParameterName . ':r)
 }else{
    let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
        context: context,
        title: NSLocalizedString("Deserialization issue",
            comment: "Deserialization issue"),
        body:"(result.value)",
        trigger:{ (selectedIndex) -> () in
    })
   reactions.append(failureReaction)
   failure(context:context)
}',2);
    }


}

$parameterEncodingString='JSON';
if($d->httpMethod=='GET'){
    $parameterEncodingString='URL';
}
    echoIndentCR(
'
    let baseURL=Bartleby.sharedInstance.getCollaborationURLForSpaceUID(spaceUID)
    let pathURL=baseURL.URLByAppendingPathComponent("'.$path.'")
    '.(($d->containsParametersOutOfPath()?'let dictionary:Dictionary<String, AnyObject>?=Mapper().toJSON(parameters)':'let dictionary:Dictionary<String, AnyObject>=[:]')).'
    let urlRequest=HTTPManager.mutableRequestWithToken(inDataSpace:spaceUID,withActionName:"'.$d->class.'" ,forMethod:"'.$d->httpMethod.'", and: pathURL)
    let r:Request=request(ParameterEncoding.'.$parameterEncodingString.'.encode(urlRequest, parameters: dictionary).0)
    r.'.(($successTypeString=='')?'responseString':'responseJSON').'{ response in

	    let request=response.request
        let result=response.result
        let response=response.response


        // Bartleby consignation

        let context = JHTTPResponse( code: '.crc32($d->class).',
            caller: "'.$d->class.'.execute",
            relatedURL:request?.URL,
            httpStatusCode: response?.statusCode ?? 0,
            response: response,
            result:result.value)

        // React according to the situation
        var reactions = Array<Bartleby.Reaction> ()
        reactions.append(Bartleby.Reaction.Track(result: result.value, context: context)) // Tracking

        if result.isFailure {
           let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                context: context,
                title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                body:NSLocalizedString("Explicit Failure",comment: "Explicit Failure"),
                trigger:{ (selectedIndex) -> () in
            })
            reactions.append(failureReaction)
            failure(context:context)

        }else{
            if let statusCode=response?.statusCode {
                if 200...299 ~= statusCode {
'.$successMicroBlock.'
            }else{
                // Bartlby does not currenlty discriminate status codes 100 & 101
                // and treats any status code >= 300 the same way
                // because we consider that failures differentiations could be done by the caller.
                let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                    context: context,
                    title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                    body:NSLocalizedString("Implicit Failure",comment: "Implicit Failure"),
                    trigger:{ (selectedIndex) -> () in
                })
               reactions.append(failureReaction)
               failure(context:context)
            }
        }
     }

     //Let s react according to the context.
     Bartleby.sharedInstance.perform(reactions, forContext: context)

  }
}
',4);

echoIndentCR('}',0)
?><?php /*<- END OF TEMPLATE */ ?>