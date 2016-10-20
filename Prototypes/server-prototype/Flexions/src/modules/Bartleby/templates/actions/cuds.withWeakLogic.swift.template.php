<?php echo GenerativeHelperForSwift::defaultHeader($flexed,$actionRepresentation); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php echo $includeBlock ?>
#endif

@objc(<?php echo$baseClassName ?>) public class <?php echo$baseClassName ?> : <?php echo GenerativeHelperForSwift::getBaseClass($flexed,$actionRepresentation); ?>,JHTTPCommand{

    // Universal type support
    override open class func typeName() -> String {
        return "<?php echo $baseClassName ?>"
    }

    fileprivate var _<?php echo$subjectName ?>:<?php echo$subjectStringType ?> = <?php echo$subjectStringType ?>()

    fileprivate var _registryUID:String=Default.NO_UID

    required public convenience init(){
        self.init(<?php echo$subjectStringType ?>(), <?php echo$registrySyntagm ?>:Default.NO_UID)
    }

<?php echo $exposedBlock?>
<?php echo $mappableBlock?>
<?php echo $secureCodingBlock?>

    /**
    This is the designated constructor.

    - parameter <?php echo $subjectName ?>: the <?php echo$subjectStringType ?> concerned the operation
    - parameter registryUID the registry or document UID

    */
    init (_ <?php echo$subjectName ?>:<?php echo$subjectStringType ?>=<?php echo$subjectStringType."()" ?>, <?php echo$registrySyntagm ?> registryUID:String) {
        self._<?php echo$subjectName ?>=<?php echo$subjectName.cr() ?>
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

    - parameter <?php echo$subjectName ?>: the instance
    - parameter registryUID:     the registry or document UID
    */
    static func commit(_ <?php echo$subjectName ?>:<?php echo$subjectStringType ?>, <?php echo$registrySyntagm ?> registryUID:String){
        let operationInstance=<?php echo$baseClassName ?>(<?php echo$subjectName ?>,<?php echo$registrySyntagm ?>:registryUID)
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
                operation.creationDate=Date()<?php echo $operationIdentificationBlock ?>
                if let currentUser=document.registryMetadata.currentUser{
                    operation.creatorUID=currentUser.UID
                    self.creatorUID=currentUser.UID
                }<?php echo $operationCommitBlock?>
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
            <?php echo$baseClassName ?>.execute(<?php echo"self._$subjectName,
                $registrySyntagm:self._registryUID,".cr() ?>
                sucessHandler: { (context: JHTTPResponse) -> () in <?php echo $distributedBlock ?>
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
                body: "\(NSLocalizedString("Attempt to push an operation with status \"",comment:"Attempt to push an operation with status =="))\(operation.status)\"" + "\n\(#file)\n\(#function)",
                onSelectedIndex: { (selectedIndex) -> () in
            })
        }
    }

    static open func execute(_ <?php echo$subjectName ?>:<?php echo$subjectStringType ?>,
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
                            body:"\(m) \n \(response)" + "\n\(#file)\n\(#function)\nhttp Status code: (\(response?.statusCode ?? 0))",
                            transmit:{ (selectedIndex) -> () in
                        })
                        reactions.append(failureReaction)
                        failure(context)
                    }else{
                        if let statusCode=response?.statusCode {
                            if 200...299 ~= statusCode {
                                // Acknowledge the trigger if there is one
                                if let dictionary = result.value as? Dictionary< String,AnyObject > {
                                    if let index=dictionary["triggerIndex"] as? NSNumber{<?php echo $acknowledgementBlock ?>
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
                                    body: "\(m) \n \(response)" + "\n\(#file)\n\(#function)\nhttp Status code: (\(response?.statusCode ?? 0))",
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