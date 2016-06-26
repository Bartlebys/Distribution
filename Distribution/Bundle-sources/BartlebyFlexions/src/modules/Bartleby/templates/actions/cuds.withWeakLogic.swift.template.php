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

$dataSpaceSyntagm='inDataSpace';


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
    $dataSpaceSyntagm='fromDataSpace';
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
                var parameters=Dictionary<String, AnyObject>()
                var collection=[Dictionary<String, AnyObject>]()

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
                var parameters=Dictionary<String, AnyObject>()
                parameters[\"ids\"]=ids".cr();
            }
            $varName=$pluralizedName;
        }else{
            $firstParameter=$parameter;
            $firstParameterName=$parameter->name;
            if($httpMethod!='DELETE'){
                $firstParameterTypeString=$ucfSingularName;
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
                parameters[\"$singularName\"]=Mapper<$firstParameterTypeString>().toJSON($firstParameterName)".cr();
            }else{
                $actionString='deleteById';
                $localAction='deleteById';
                $firstParameterTypeString='String';
                $executeArgumentSerializationBlock="
                var parameters=Dictionary<String, AnyObject>()
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
    override public class func typeName() -> String {
        return "<?php echo $baseClassName ?>"
    }

    private var _<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?> = <?php echo$firstParameterTypeString ?>()

    // The dataSpace UID
    private var _spaceUID:String=Default.NO_UID

    // The operation
    private var _operation:Operation=Operation()

    required public convenience init(){
        self.init(<?php echo$firstParameterTypeString ?>(), <?php echo$dataSpaceSyntagm ?>:Default.NO_UID)
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
$virtualEntity=new EntityRepresentation();

$_ENTITY_rep=new PropertyRepresentation();
$_ENTITY_rep->name='_'.$firstParameterName;


$_ENTITY_rep->type=$firstParameter->type;
$_ENTITY_rep->instanceOf=$firstParameter->instanceOf;
$_ENTITY_rep->required=true;
$_ENTITY_rep->isDynamic=false;
$_ENTITY_rep->default=NULL;
$_ENTITY_rep->isGeneratedType=true;
$virtualEntity->properties[]=$_ENTITY_rep;


$_spaceUID_rep=new PropertyRepresentation();
$_spaceUID_rep->name="_spaceUID";
$_spaceUID_rep->type=FlexionsTypes::STRING;
$_spaceUID_rep->required=true;
$_spaceUID_rep->isDynamic=false;
$_spaceUID_rep->default="Default.NO_UID";
$_spaceUID_rep->isGeneratedType=false;
$virtualEntity->properties[]=$_spaceUID_rep;


// Operation is a very special object.
// Used By bartleby interact with a collaborative api
// (!) Do not serialize globally the operation
// as the operation will serialize this instance in its data dictionary.

$_opUID_operation_rep=new PropertyRepresentation();
$_opUID_operation_rep->name="_operation.spaceUID";
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
$_status_operation_rep->emumPreciseType="Operation.Status";
$_status_operation_rep->required=true;
$_status_operation_rep->default='.None';
$_status_operation_rep->isGeneratedType=true;

$_counter_operation_rep=new PropertyRepresentation();
$_counter_operation_rep->name="_operation.counter";
$_counter_operation_rep->type=FlexionsTypes::INTEGER;
$_counter_operation_rep->required=false;
$_counter_operation_rep->isGeneratedType=true;

$_creationDate_operation_rep=new PropertyRepresentation();
$_creationDate_operation_rep->name="_operation.creationDate";
$_creationDate_operation_rep->type=FlexionsTypes::DATETIME;
$_creationDate_operation_rep->required=false;
$_creationDate_operation_rep->isGeneratedType=true;

$_baseUrl_operation_rep=new PropertyRepresentation();
$_baseUrl_operation_rep->name="_operation.baseUrl";
$_baseUrl_operation_rep->type=FlexionsTypes::URL;
$_baseUrl_operation_rep->required=false;
$_baseUrl_operation_rep->isGeneratedType=true;

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
                                                    $_creationDate_operation_rep,
                                                    $_baseUrl_operation_rep
                                                );
$virtualEntity->properties[]=$_operation_rep;
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
    - parameter spaceUID the space UID

    */
    init (_ <?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>=<?php echo$firstParameterTypeString."()" ?>, <?php echo$dataSpaceSyntagm ?> spaceUID:String) {
        self._<?php echo$firstParameterName ?>=<?php echo$firstParameterName.cr() ?>
        self._spaceUID=spaceUID
        super.init()
    }

    /**
    Creates the operation and proceeds to commit

    - parameter <?php echo$firstParameterName ?>: the instance
    - parameter spaceUID:     the space UID
    */
    static func commit(<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>, <?php echo$dataSpaceSyntagm ?> spaceUID:String){
        let operationInstance=<?php echo$baseClassName ?>(<?php echo$firstParameterName ?>,<?php echo$dataSpaceSyntagm ?>:spaceUID)
        operationInstance.commit()
    }


    func commit(){
        let context=Context(code:<?php echo crc32($baseClassName.'.commit') ?>, caller: "<?php echo$baseClassName ?>.commit")
        if let document = Bartleby.sharedInstance.getDocumentByUID(self._spaceUID) {

                // Prepare the operation serialization
                self.defineUID()
                self._operation.defineUID()
                self._operation.counter=0
                self._operation.status=Operation.Status.Pending
                self._operation.baseUrl=document.registryMetadata.collaborationServerURL
                self._operation.creationDate=NSDate()
                self._operation.spaceUID=self._spaceUID
<?php
if ($httpMethod=="DELETE"){
    if ($parameter->type == FlexionsTypes::COLLECTION){
        echoIndentCR('                let stringIDS=PString.ltrim(self._'.$firstParameterName.'.reduce("", combine: { $0+","+$1 }),characters:",")',0);
        echoIndentCR('                self._operation.summary="'.$baseClassName.'(\(stringIDS))"',0);
    }else{
        echoIndentCR('                self._operation.summary="'.$baseClassName.'(\(self._'.$firstParameterName.'))"',0);
    }
}else{
    if ($parameter->type == FlexionsTypes::COLLECTION){
        echoIndentCR('                let stringIDS=PString.ltrim(self._'.$firstParameterName.'.reduce("", combine: { $0+","+$1.UID }),characters:",")',0);
        echoIndentCR('                self._operation.summary="'.$baseClassName.'(\(stringIDS))"',0);
    }else{
        echoIndentCR('                self._operation.summary="'.$baseClassName.'(\(self._'.$firstParameterName.'.UID))"',0);
    }
}
?>

                if let currentUser=document.registryMetadata.currentUser{
                    self._operation.creatorUID=currentUser.UID
                    self.creatorUID=currentUser.UID
                }

                // Provision the operation.
                do{
                    let ic:OperationsCollectionController = try document.getCollection()
                    ic.add(self._operation, commit:false)
                }catch{
                    Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: "Structural Error",
                    body: "Operation collection is missing",
                    onSelectedIndex: { (selectedIndex) -> () in
                    })
                }
                self._operation.toDictionary=self.dictionaryRepresentation()
        <?php
            if ($httpMethod!="DELETE"){
                if ($parameter->type == FlexionsTypes::COLLECTION){
                    echo("
                for item in self._$firstParameterName{
                     item.committed=true
                 }".cr());
                }else{
                    echo("
                self._$firstParameterName.committed=true".cr());
                }
            }
        ?>
        }else{
            // This document is not available there is nothing to do.
            let m=NSLocalizedString("Registry is missing", comment: "Registry is missing")
            Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: NSLocalizedString("Structural error", comment: "Structural error"),
                    body: "\(m) spaceUID=\(self._spaceUID)",
                    onSelectedIndex: { (selectedIndex) -> () in
                    }
            )
        }
    }

    public func push(sucessHandler success:(context:JHTTPResponse)->(),
        failureHandler failure:(context:JHTTPResponse)->()){
        if let <?php if($httpMethod=="POST"){echo("document");}else{echo("_");} ?> = Bartleby.sharedInstance.getDocumentByUID(self._spaceUID) {
            // The unitary operation are not always idempotent
            // so we do not want to push multiple times unintensionnaly.
            if  self._operation.status==Operation.Status.Pending ||
                self._operation.status==Operation.Status.Unsucessful {
                // We try to execute
                self._operation.status=Operation.Status.InProgress
                <?php echo$baseClassName ?>.execute(<?php echo"self._$firstParameterName,
                    $dataSpaceSyntagm:self._spaceUID,".cr() ?>
                    sucessHandler: { (context: JHTTPResponse) -> () in
                        <?php if ($httpMethod=="POST") {
                            echo("document.markAsDistributed(&self._$firstParameterName)".cr());
                        } else {
                            echo(cr());
                        }
                        ?>
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Successful
                        self._operation.responseDictionary=Mapper<JHTTPResponse>().toJSON(context)
                        self._operation.lastInvocationDate=NSDate()
                        success(context:context)
                    },
                    failureHandler: {(context: JHTTPResponse) -> () in
                        self._operation.counter=self._operation.counter!+1
                        self._operation.status=Operation.Status.Unsucessful
                        self._operation.responseDictionary=Mapper<JHTTPResponse>().toJSON(context)
                        self._operation.lastInvocationDate=NSDate()
                        failure(context:context)
                    }
                )
            }else{
                // This document is not available there is nothing to do.
                let context=Context(code:<?php echo crc32($baseClassName.'.push') ?>, caller: "<?php echo$baseClassName ?>.push")
                Bartleby.sharedInstance.dispatchAdaptiveMessage(context,
                    title: NSLocalizedString("Push error", comment: "Push error"),
                    body: "\(NSLocalizedString("Attempt to push an operation with status ==",comment:"Attempt to push an operation with status =="))\(self._operation.status))",
                    onSelectedIndex: { (selectedIndex) -> () in
                })
            }
        }
    }

    static public func execute(<?php echo$firstParameterName ?>:<?php echo$firstParameterTypeString ?>,
<?php echo$dataSpaceSyntagm ?> spaceUID:String,
            sucessHandler success:(context:JHTTPResponse)->(),
            failureHandler failure:(context:JHTTPResponse)->()){
                let baseURL=Bartleby.sharedInstance.getCollaborationURLForSpaceUID(spaceUID)
                let pathURL=baseURL.URLByAppendingPathComponent("<?php echo$varName ?>")<?php echo $executeArgumentSerializationBlock?>
                let urlRequest=HTTPManager.mutableRequestWithToken(inDataSpace:spaceUID,withActionName:"<?php echo$baseClassName ?>" ,forMethod:"<?php echo$httpMethod?>", and: pathURL)
                let r:Request=request(ParameterEncoding.JSON.encode(urlRequest, parameters: parameters).0)
                r.responseJSON{ response in

                    // Store the response
                    let request=response.request
                    let result=response.result
                    let response=response.response

                    // Bartleby consignation
                    let context = JHTTPResponse( code: <?php echo crc32($baseClassName.'.execute') ?>,
                        caller: "<?php echo$baseClassName ?>.execute",
                        relatedURL:request?.URL,
                        httpStatusCode: response?.statusCode ?? 0,
                        response: response,
                        result:result.value)

                    // React according to the situation
                    var reactions = Array<Bartleby.Reaction> ()
                    reactions.append(Bartleby.Reaction.Track(result: result.value, context: context)) // Tracking

                    if result.isFailure {
                        let m = NSLocalizedString("<?php echo$actionString ?>  of <?php echo$varName ?>",
                            comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description")
                        let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                            context: context,
                            title: NSLocalizedString("Unsuccessfull attempt result.isFailure is true",
                            comment: "Unsuccessfull attempt"),
                            body:"\(m) \n \(response)" ,
                            transmit:{ (selectedIndex) -> () in
                        })
                        reactions.append(failureReaction)
                        failure(context:context)
                    }else{
                        if let statusCode=response?.statusCode {
                            if 200...299 ~= statusCode {
                                // Acknowledge the trigger and log QA issue
                                if let dictionary = result.value as? Dictionary< String,AnyObject > {
                                    if let index=dictionary["triggerIndex"] as? NSNumber{
                                        if let document=Bartleby.sharedInstance.getDocumentByUID(spaceUID){
                                            document.acknowledgeOwnedTriggerIndex(index.integerValue)
                                        }
                                    }else{
                                        bprint("QA Trigger index is missing \(context)", file: #file, function: #function, line: #line, category:bprintCategoryFor(Trigger))
                                    }
                                }else{
                                    bprint("QA Trigger index dictionary is missing \(context)", file: #file, function: #function, line: #line, category:bprintCategoryFor(Trigger))
                                }
                                success(context:context)
                            }else{
                                // Bartlby does not currenlty discriminate status codes 100 & 101
                                // and treats any status code >= 300 the same way
                                // because we consider that failures differentiations could be done by the caller.

                                let m=NSLocalizedString("<?php echo$actionString ?> of <?php echo$varName ?>",
                                        comment: "<?php echo$actionString ?> of <?php echo$varName ?> failure description")
                                let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                                    context: context,
                                    title: NSLocalizedString("Unsuccessfull attempt",
                                    comment: "Unsuccessfull attempt"),
                                    body: "\(m) \n \(response)",
                                    transmit:{ (selectedIndex) -> () in
                                    })
                                reactions.append(failureReaction)
                                failure(context:context)
                            }
                        }
                     }
                    //Let's react according to the context.
                    Bartleby.sharedInstance.perform(reactions, forContext: context)
                }
            }
}
<?php /*<- END OF TEMPLATE */?>