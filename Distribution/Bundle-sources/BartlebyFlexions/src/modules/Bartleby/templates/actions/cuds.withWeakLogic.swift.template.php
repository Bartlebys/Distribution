<?php

/*
 * SWIFT 2.X template
 * This weak logic template is compliant with Bartleby 1.0 approach.
 * It allows to update easily very complex templates.gt
 * It is not logic less but the logic intent to be as weak as possible
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $flexed Flexed */
/* @var $actionRepresentation ActionRepresentation*/
/* @var $hypotypose Hypotypose */

if (isset( $f,$d,$h)) {

    /* @var $f Flexed */
    /* @var $d ActionRepresentation*/
    /* @var $h Hypotypose */

    // We use explicit name (!)
    // And reserve $f , $d , $h possibly for blocks

    /* @var $flexed Flexed*/
    /* @var $actionRepresentation ActionRepresentation*/
    /* @var $hypotypose Hypotypose*/

    $flexed=$f;
    $actionRepresentation=$d;
    $hypotypose=$h;

    $flexed->fileName = $actionRepresentation->class . '.swift';
    $flexed->package = 'xOS/operations/';

}else{
    return NULL;
}

/////////////////
// EXCLUSIONS
/////////////////

// Should this Action be excluded ?

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}


// This template cannot be used for GET Methods
if ($actionRepresentation->httpMethod==='GET'){
    return NULL;
}

// We want also to exclude by query

if (!(strpos($d->class,'ByQuery')===false)){
    return NULL;
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

// Compute ALL the Variables you need in the template

$httpMethod=$actionRepresentation->httpMethod;
$pluralizedName=lcfirst($actionRepresentation->collectionName);
$singularName=lcfirst(Pluralization::singularize($pluralizedName));
$baseClassName=ucfirst($actionRepresentation->class);
$ucfSingularName=ucfirst($singularName);
$ucfPluralizedName=ucfirst($pluralizedName);

$actionString=NULL;
$localAction=NULL;

$registrySyntagm='inRegistryWithUID';


if ($httpMethod=="POST"){
    $actionString='creation';
    $localAction='upsert';
}elseif ($httpMethod=="PUT"){
    $actionString='update';
    $localAction='upsert';
}elseif ($httpMethod=="PATCH"){
    $actionString='update';
    $localAction='upsert';
}elseif ($httpMethod=="DELETE"){
    $actionString=NULL;
    $localAction=NULL;
    $registrySyntagm='fromRegistryWithUID';
}else{
    $actionString='NO_FOUND';
    $localAction='NO_FOUND';
}

$firstParameterName=NULL;
$firstParameterTypeString=NULL;
$varName=NULL;
$executeArgumentSerializationBlock=NULL;
/* @var $firstParameter PropertyRepresentation */
$firstParameter=NULL;


while($actionRepresentation->iterateOnParameters()){
    /*@var $parameter PropertyRepresentation*/
    $parameter=$actionRepresentation->getParameter();
    // We use the first parameter.
    if (!isset($varName,$firstParameterName,$firstParameterTypeString)){
        if ($parameter->type == FlexionsTypes::COLLECTION){
            $firstParameter=$parameter;
            $firstParameterName=$parameter->name;
            if($httpMethod!='DELETE'){
                $firstParameterTypeString='['.$ucfSingularName.']';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, Any>()
                var collection=[Dictionary<String, Any>]()

                for $singularName in $pluralizedName{
                    let serializedInstance=Mapper<$ucfSingularName>().toJSON($singularName)
                    collection.append(serializedInstance)
                }
                parameters[\"$pluralizedName\"]=collection".cr();
            }else{
                $actionString='deleteByIds';
                $localAction='deleteByIds';
                $firstParameterTypeString='[String]';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, Any>()
                parameters[\"ids\"]=ids".cr();
            }
            $varName=$pluralizedName;
        }else{
            $firstParameter=$parameter;
            $firstParameterName=$parameter->name;
            if($httpMethod!='DELETE'){
                $firstParameterTypeString=$ucfSingularName;
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, Any>()
                parameters[\"$singularName\"]=Mapper<$firstParameterTypeString>().toJSON($firstParameterName)".cr();
            }else{
                $actionString='deleteById';
                $localAction='deleteById';
                $firstParameterTypeString='String';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, Any>()
                parameters[\"".$singularName."Id\"]=".$singularName."Id".cr();
            }
            $varName=$singularName;
        }
    }
}


/////////////////////////
// TEMPLATE
/////////////////////////

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($flexed,$actionRepresentation); ?>

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
/*@var array */
$GLOBAL_GENERATED_ACTIONS[]=$baseClassName;

?>
#endif

@objc(<?php echo$baseClassName ?>) public class <?php echo$baseClassName ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d); ?>,JHTTPCommand{

    // Universal type support
    override open class func typeName() -> String {
        return "<?php echo $baseClassName ?>"
    }

    fileprivate var _<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?> = <?php echo$firstParameterTypeString ?>()

    fileprivate var _registryUID:String=Default.NO_UID

    required public convenience init(){
        self.init(<?php echo$firstParameterTypeString ?>(), <?php echo$registrySyntagm ?>:Default.NO_UID)
    }



<?php

//////////////////////////////
//
// THIS IS A COMPLEX CASE
// READ CAREFULLY
//
// We want to serialize the parameters has Mappable & NSSecureCoding
// and  not serialize globally the operation
// as the operation will serialize this instance in its data dictionary.
//
// We Gonna inject the relevant private properties.
// #1 Create a virtual entity
// #2 Inject the PropertyRepresentation
////////////////////////////////

/* @var $virtualEntity EntityRepresentation */

$privateMemberName='_'.$firstParameterName;

$virtualEntity=new EntityRepresentation();

$_ENTITY_rep=new PropertyRepresentation();
$_ENTITY_rep->name=$privateMemberName;


$_ENTITY_rep->type=$firstParameter->type;
$_ENTITY_rep->instanceOf=$firstParameter->instanceOf;
$_ENTITY_rep->required=true;
$_ENTITY_rep->isDynamic=false;
$_ENTITY_rep->default=NULL;
$_ENTITY_rep->isGeneratedType=true;
$virtualEntity->properties[]=$_ENTITY_rep;


$_spaceUID_rep=new PropertyRepresentation();
$_spaceUID_rep->name="_registryUID";
$_spaceUID_rep->type=FlexionsTypes::STRING;
$_spaceUID_rep->required=true;
$_spaceUID_rep->isDynamic=false;
$_spaceUID_rep->default="Default.NO_UID";
$_spaceUID_rep->isGeneratedType=false;
$virtualEntity->properties[]=$_spaceUID_rep;

/*

// Operation is a very special object.
// Used By bartleby interact with a collaborative api
// (!) Do not serialize globally the operation
// as the operation will serialize this instance in its data dictionary.

$_opUID_operation_rep=new PropertyRepresentation();
$_opUID_operation_rep->name="_operation.registryUID";
$_opUID_operation_rep->type=FlexionsTypes::STRING;
$_opUID_operation_rep->required=true;
$_opUID_operation_rep->default="\\(Default.NO_UID)";
$_opUID_operation_rep->isGeneratedType=true;

$_creatorUID_operation_rep=new PropertyRepresentation();
$_creatorUID_operation_rep->name="_operation.creatorUID";
$_creatorUID_operation_rep->type=FlexionsTypes::STRING;
$_creatorUID_operation_rep->required=true;
$_creatorUID_operation_rep->default="\\(Default.NO_UID)";
$_creatorUID_operation_rep->isGeneratedType=true;

$_status_operation_rep=new PropertyRepresentation();
$_status_operation_rep->name="_operation.status";
$_status_operation_rep->type=FlexionsTypes::ENUM;
$_status_operation_rep->instanceOf="string";
$_status_operation_rep->emumPreciseType="PushOperation.Status";
$_status_operation_rep->required=true;
$_status_operation_rep->default='.None';
$_status_operation_rep->isGeneratedType=true;

$_counter_operation_rep=new PropertyRepresentation();
$_counter_operation_rep->name="_operation.counter";
$_counter_operation_rep->type=FlexionsTypes::INTEGER;
$_counter_operation_rep->required=true;
$_counter_operation_rep->default=0;
$_counter_operation_rep->isGeneratedType=true;

$_creationDate_operation_rep=new PropertyRepresentation();
$_creationDate_operation_rep->name="_operation.creationDate";
$_creationDate_operation_rep->type=FlexionsTypes::DATETIME;
$_creationDate_operation_rep->required=false;
$_creationDate_operation_rep->isGeneratedType=true;


$_operation_rep=new PropertyRepresentation();
$_operation_rep->name="_operation";
$_operation_rep->type="Operation";
$_operation_rep->required=true;
$_operation_rep->isDynamic=false;
$_operation_rep->default="Operation()";
$_operation_rep->isGeneratedType=true;



// So we use a customSerializationMapping
$_operation_rep->customSerializationMapping=array(
                                                    $_opUID_operation_rep,
                                                    $_creatorUID_operation_rep,
                                                    $_status_operation_rep,
                                                    $_counter_operation_rep,
                                                    $_creationDate_operation_rep
                                                );
$virtualEntity->properties[]=$_operation_rep;
*/
$blockRepresentation=$virtualEntity;

// Mappable
include  FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php';
if( $modelsShouldConformToNSCoding ) {
    // NSSecureCoding
    include  FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
}

?>


    /**
    This is the designated constructor.

    - parameter <?php echo$firstParameterName ?>: the <?php echo$firstParameterName ?> concerned the operation
    - parameter registryUID the registry or document UID

    */
    init (_ <?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>=<?php echo$firstParameterTypeString."()" ?>, <?php echo$registrySyntagm ?> registryUID:String) {
        self._<?php echo$firstParameterName ?>=<?php echo$firstParameterName.cr() ?>
        self._registryUID=registryUID
        super.init()
    }

    /**
     Returns an operation with self.UID as commandUID

     - returns: return the operation
     */
    fileprivate func _getOperation()->PushOperation{
        if let document = Bartleby.sharedInstance.getDocumentByUID(self._registryUID) {
            if let ic:PushOperationsCollectionController = try? document.getCollection(){
                let operations=ic.filter({ (operation) -> Bool in
                    return operation.commandUID==self.UID
                })
                if let operation=operations.first {
                    return operation
                }}
        }
        let operation=PushOperation()
        operation.disableSupervision()
        operation.commandUID=self.UID
        operation.defineUID()
        return operation
    }


    /**
    Creates the operation and proceeds to commit

    - parameter <?php echo$firstParameterName ?>: the instance
    - parameter registryUID:     the registry or document UID
    */
    static func commit(_ <?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>, <?php echo$registrySyntagm ?> registryUID:String){
        let operationInstance=<?php echo$baseClassName ?>(<?php echo$firstParameterName ?>,<?php echo$registrySyntagm ?>:registryUID)
        operationInstance.commit()
    }


    func commit(){
        let context=Context(code:<?php echo crc32($baseClassName.'.commit') ?>, caller: "<?php echo$baseClassName ?>.commit")
        if let document = Bartleby.sharedInstance.getDocumentByUID(self._registryUID) {
            // Provision the operation.
            do{
                let ic:PushOperationsCollectionController = try document.getCollection()
                let operation=self._getOperation()
                operation.counter += 1
                operation.status=PushOperation.Status.pending
                operation.creationDate=Date()
<?php
if ($httpMethod=="DELETE"){
    if ($parameter->type == FlexionsTypes::COLLECTION){
        echoIndentCR('                let stringIDS=PString.ltrim(self._'.$firstParameterName.'.reduce("", { $0+","+$1 }),characters:",")',0);
        echoIndentCR('                operation.summary="'.$baseClassName.'(\(stringIDS))"',0);
    }else{
        echoIndentCR('                operation.summary="'.$baseClassName.'(\(self._'.$firstParameterName.'))"',0);
    }
}else{
    if ($parameter->type == FlexionsTypes::COLLECTION){
        echoIndentCR('                let stringIDS=PString.ltrim(self._'.$firstParameterName.'.reduce("", { $0+","+$1.UID }),characters:",")',0);
        echoIndentCR('                operation.summary="'.$baseClassName.'(\(stringIDS))"',0);
    }else{
        echoIndentCR('                operation.summary="'.$baseClassName.'(\(self._'.$firstParameterName.'.UID))"',0);
    }
}
?>
                if let currentUser=document.registryMetadata.currentUser{
                    operation.creatorUID=currentUser.UID
                    self.creatorUID=currentUser.UID
                }
<?php
    if ($httpMethod!="DELETE"){
        if ($parameter->type == FlexionsTypes::COLLECTION){
            echo("                for item in self._$firstParameterName{
                    item.committed=true
                }".cr());
        }else{
            echo("                self._$firstParameterName.committed=true".cr());
        }
    }?>
                operation.toDictionary=self.dictionaryRepresentation()
                operation.enableSupervision()
                ic.add(operation, commit:false)
            }catch{
                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: "Structural Error",
                    body: "Operation collection is missing in  <?php echo($baseClassName);?>",
                    onSelectedIndex: { (selectedIndex) -> () in
                })
            }
        }else{
            // This document is not available there is nothing to do.
            let m=NSLocalizedString("Registry is missing", comment: "Registry is missing")
            Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: NSLocalizedString("Structural error", comment: "Structural error"),
                    body: "\(m) registryUID =\(self._registryUID) in <?php echo($baseClassName);?>",
                    onSelectedIndex: { (selectedIndex) -> () in
                    }
            )
        }
    }

    open func push(sucessHandler success:@escaping (_ context:JHTTPResponse)->(),
        failureHandler failure:@escaping (_ context:JHTTPResponse)->()){
        // The unitary operation are not always idempotent
        // so we do not want to push multiple times unintensionnaly.
        // Check BartlebyDocument+Operations.swift to understand Operation status
        let operation=self._getOperation()
        if  operation.canBePushed(){
            // We try to execute
            operation.status=PushOperation.Status.inProgress
            <?php echo$baseClassName ?>.execute(<?php echo"self._$firstParameterName,
                $registrySyntagm:self._registryUID,".cr() ?>
                sucessHandler: { (context: JHTTPResponse) -> () in
<?php
                if ($httpMethod!="DELETE"){
                    if ($parameter->type == FlexionsTypes::COLLECTION){
                        echo("                    for item in self._$firstParameterName{
                        item.distributed=true
                    }".cr());
                    }else{
                        echo("                    self._$firstParameterName.distributed=true".cr());
                    }
                }
?>
                    operation.counter=operation.counter+1
                    operation.status=PushOperation.Status.completed
                    operation.responseDictionary=Mapper<JHTTPResponse>().toJSON(context)
                    operation.lastInvocationDate=Date()
                    let completion=Completion.successStateFromJHTTPResponse(context)
                    completion.setResult(context)
                    operation.completionState=completion
                    success(context)
                },
                failureHandler: {(context: JHTTPResponse) -> () in
                    operation.counter=operation.counter+1
                    operation.status=PushOperation.Status.completed
                    operation.responseDictionary=Mapper<JHTTPResponse>().toJSON(context)
                    operation.lastInvocationDate=Date()
                    let completion=Completion.failureStateFromJHTTPResponse(context)
                    completion.setResult(context)
                    operation.completionState=completion
                    failure(context)
                }
            )
        }else{
            // This document is not available there is nothing to do.
            let context=Context(code:<?php echo crc32($baseClassName.'.push') ?>, caller: "<?php echo$baseClassName ?>.push")
            Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                title: NSLocalizedString("Push error", comment: "Push error"),
                body: "\(NSLocalizedString("Attempt to push an operation with status \"",comment:"Attempt to push an operation with status =="))\(operation.status)\"",
                onSelectedIndex: { (selectedIndex) -> () in
            })
        }
    }

    static open func execute(_ <?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>,
            <?php echo$registrySyntagm ?> registryUID:String,
            sucessHandler success: @escaping(_ context:JHTTPResponse)->(),
            failureHandler failure: @escaping(_ context:JHTTPResponse)->()){
            if let document = Bartleby.sharedInstance.getDocumentByUID(registryUID) {
                let pathURL = document.baseURL.appendingPathComponent("<?php echo$varName ?>")<?php echo $executeArgumentSerializationBlock?>
                let urlRequest=HTTPManager.requestWithToken(inRegistryWithUID:document.UID,withActionName:"<?php echo$baseClassName ?>" ,forMethod:"<?php echo$httpMethod?>", and: pathURL)
                do {
                    let r=try <?php if ($httpMethod=='GET') {echo"URLEncoding()";}else{echo"JSONEncoding()";}?>.encode(urlRequest,with:parameters)
                    request(r).validate().responseJSON(completionHandler: { (response) in

                    // Store the response
                    let request=response.request
                    let result=response.result
                    let response=response.response

                    // Bartleby consignation
                    let context = JHTTPResponse( code: <?php echo crc32($baseClassName.'.execute') ?>,
                        caller: "<?php echo$baseClassName ?>.execute",
                        relatedURL:request?.url,
                        httpStatusCode: response?.statusCode ?? 0,
                        response: response,
                        result:result.value)

                    // React according to the situation
                    var reactions = Array<Bartleby.Reaction> ()
                    reactions.append(Bartleby.Reaction.track(result: result.value, context: context)) // Tracking

                    if result.isFailure {
                        let m = NSLocalizedString("<?php echo$actionString ?>  of <?php echo$varName ?>",
                            comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description")
                        let failureReaction =  Bartleby.Reaction.dispatchAdaptiveMessage(
                            context: context,
                            title: NSLocalizedString("Unsuccessfull attempt result.isFailure is true",
                            comment: "Unsuccessfull attempt"),
                            body:"\(m) \n \(response)" ,
                            transmit:{ (selectedIndex) -> () in
                        })
                        reactions.append(failureReaction)
                        failure(context)
                    }else{
                        if let statusCode=response?.statusCode {
                            if 200...299 ~= statusCode {
                                // Acknowledge the trigger if there is one
                                if let dictionary = result.value as? Dictionary< String,AnyObject > {
                                    if let index=dictionary["triggerIndex"] as? NSNumber{
                                        document.acknowledgeOwnedTriggerIndex(index.intValue)
                                    }
                                }
                                success(context)
                            }else{
                                // Bartlby does not currenlty discriminate status codes 100 & 101
                                // and treats any status code >= 300 the same way
                                // because we consider that failures differentiations could be done by the caller.

                                let m=NSLocalizedString("<?php echo$actionString ?> of <?php echo$varName ?>",
                                        comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description")
                                let failureReaction =  Bartleby.Reaction.dispatchAdaptiveMessage(
                                    context: context,
                                    title: NSLocalizedString("Unsuccessfull attempt",
                                    comment: "Unsuccessfull attempt"),
                                    body: "\(m) \n \(response)",
                                    transmit:{ (selectedIndex) -> () in
                                    })
                                reactions.append(failureReaction)
                                failure(context)
                            }
                        }
                     }
                    //Let's react according to the context.
                    Bartleby.sharedInstance.perform(reactions, forContext: context)
                })
                }catch{
                    let context = JHTTPResponse( code:2 ,
                    caller: "<?php echo$baseClassName ?>.execute",
                    relatedURL:nil,
                    httpStatusCode:500,
                    response:nil,
                    result:"{\"message\":\"\(error)}")
                    failure(context)
                }

            }else{
                let context = JHTTPResponse( code:1 ,
                    caller: "<?php echo$baseClassName ?>.execute",
                    relatedURL:nil,
                    httpStatusCode:417,
                    response:nil,
                    result:"{\"message\":\"Unexisting document with registryUID \(registryUID)\"}")
                    failure(context)
            }
        }
}
<?php /*<- END OF TEMPLATE */?>