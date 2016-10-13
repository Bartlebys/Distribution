<?php

include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/project/SwiftDocumentConfigurator.php';

/*
 * This template is an advanced template that must be configured
 * to be used multiple time within the same project
 * You must declare $configurator a SwitfDocumentConfigurator instance its before invocation.
 */

/* @var $f Flexed */
/* @var $d ProjectRepresentation */
/* @var $project ProjectRepresentation */
/* @var $action ActionRepresentation*/
/* @var $entity EntityRepresentation */
/* @var $configurator SwiftDocumentConfigurator */

if (isset ( $f ) && isset($configurator)) {
    // We determine the file name.
    $f->fileName = $configurator->filename;
    // And its package.
    $f->package = 'xOS/';
}else{
    return 'THIS TEMPLATES REQUIRES A SwitfDocumentConfigurator IN $configurator';
}


$project=$d;// It is a project template

//Collection controllers are related to actions.


/* TEMPLATES STARTS HERE -> */?>
//
//  <?php echo($configurator->filename.cr()) ?>
//
//  The is the central piece of the Document oriented architecture.
//  We provide a universal implementation with conditionnal compilation
//
//  The document stores references to Bartleby's style CollectionControllers.
//  This allow to use intensively bindings and distributed data automation.
//  With the mediation of standard Bindings approach with NSArrayControler
//
//  We prefer to centralize the complexity of data handling in the document.
//  Thats why for example we implement projectBindingsArrayController.didSet with an CGD dispatching
//  We could have set the binding programmatically in the WindowController
//  But we consider for clarity that the Storyboarded Bindings Settings should be as exhaustive as possible.
//  And the potential complexity masked.
//
//  Generated by flexions
//

import Foundation

#if os(OSX)
import AppKit
#else
import UIKit
#endif

#if !USE_EMBEDDED_MODULES
import ObjectMapper
#endif

<?php
if ($isIncludeInBartlebysCommons==false) {
    echoIndentCR('#if !USE_EMBEDDED_MODULES', 0);
    echoIndentCR('import BartlebyKit', 0);
    echoIndentCR('#endif', 0);
}
?>


@objc(<?php echo($configurator->getClassName())?>) open class <?php echo($configurator->getClassName())?> : <?php
    if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
        echo('Registry');
    }else{
        echo('BartlebyDocument');
    }
?> {

    #if os(OSX)

    required public init() {
        super.init()
        <?php echo($configurator->getClassName())?>.declareTypes()
    }

    #else

    private var _fileURL: URL

    override public init(fileURL url: URL) {
        self._fileURL = url
        super.init(fileURL: url)
        <?php echo($configurator->getClassName())?>.declareTypes()
    }
    #endif


    // MARK  Universal Type Support

    override open class func declareTypes() {
        super.declareTypes()
<?php
// ENTITIES

/*@ Hypotypose */
$hypotypose=$h;
$list=$hypotypose->getFlatFlexedList();

// FLEXED
$declareAllCollectibileType=false;
if ($declareAllCollectibileType==true) {
    foreach ($list as $flexed) {
        /*@var Flexed */
        $flexedElement = $flexed;
        $fileName = $flexedElement->fileName;
        if ((strpos($fileName, '.swift') !== false) && (strpos($fileName, "Abstract") === false && strpos($fileName, "Document") === false)) {
            $fileName = str_replace('.swift', '', $fileName);
            echoIndentCR('Registry.declareCollectibleType(' . $fileName . ')', 2);
            echoIndentCR('Registry.declareCollectibleType(Alias<' . $fileName . '>)', 2);
        }
    }
}

?>
    }


    // MARK: - Collection Controllers

    fileprivate var _KVOContext: Int = 0

    // The initial instances are proxies
    // On document deserialization the collection are populated.

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $collectionControllerClassName=ucfirst($pluralizedEntity).'CollectionController';
        $collectionControllerVariableName=lcfirst($pluralizedEntity).'CollectionController';
        echoIndentCR('open dynamic var '.lcfirst($pluralizedEntity).'='.$collectionControllerClassName.'(){',1);
        echoIndentCR('willSet{',2);
        echoIndentCR(lcfirst($pluralizedEntity).'.document=self',3);
        echoIndentCR('}',2);
        echoIndentCR('}',1);
        echoIndentCR('',1);
    }
}
?>

    // MARK: - Array Controllers and automation (OSX)
 #if os(OSX) && !USE_EMBEDDED_MODULES


    // KVO
    // Those array controllers are Owned by their respective ViewControllers
    // Those view Controller are observed here to insure a consistent persitency


<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        echoIndentCR('

    open var '.$arrayControllerVariableName.': NSArrayController?{

        willSet{
            // Remove observer on previous array Controller
            '.lcfirst($arrayControllerVariableName).'?.removeObserver(self, forKeyPath: "selectionIndexes", context: &self._KVOContext)
        }
        didSet{
            // Setup the Array Controller in the CollectionController
            self.'.lcfirst($pluralizedEntity).'.arrayController='.lcfirst($arrayControllerVariableName).'
            // Add observer
            '.lcfirst($arrayControllerVariableName).'?.addObserver(self, forKeyPath: "selectionIndexes", options: .new, context: &self._KVOContext)
            if let indexes=self.registryMetadata.stateDictionary['.$configurator->getClassName().'.kSelected'.ucfirst($pluralizedEntity).'IndexesKey] as? [Int]{
                let indexesSet = NSMutableIndexSet()
                indexes.forEach{ indexesSet.add($0) }
                self.'.lcfirst($arrayControllerVariableName).'?.setSelectionIndexes(indexesSet as IndexSet)
             }
        }
    }
        ',0);
    }
}
?>


#endif

    // indexes persistency

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        echoIndentCR('
    
    static open let kSelected'.ucfirst($pluralizedEntity).'IndexesKey="selected'.ucfirst($pluralizedEntity).'IndexesKey"
    static open let '.strtoupper($pluralizedEntity).'_SELECTED_INDEXES_CHANGED_NOTIFICATION="'.strtoupper($pluralizedEntity).'_SELECTED_INDEXES_CHANGED_NOTIFICATION"
    dynamic open var selected'.ucfirst($pluralizedEntity).':['.ucfirst($entity->name).']?{
        didSet{
            if let '.lcfirst($pluralizedEntity).' = selected'.ucfirst($pluralizedEntity).' {
                 let indexes:[Int]='.lcfirst($pluralizedEntity).'.map({ ('.lcfirst($entity->name).') -> Int in
                    return self.'.lcfirst($pluralizedEntity).'.index(where:{ return $0.UID == '.lcfirst($entity->name).'.UID })!
                })
                self.registryMetadata.stateDictionary['.$configurator->getClassName().'.kSelected'.ucfirst($pluralizedEntity).'IndexesKey]=indexes
                NotificationCenter.default.post(name:NSNotification.Name(rawValue:'.$configurator->getClassName().'.'.strtoupper($pluralizedEntity).'_SELECTED_INDEXES_CHANGED_NOTIFICATION), object: nil)
            }
        }
    }
    var firstSelected'.ucfirst($entity->name).':'.ucfirst($entity->name).'? { return self.selected'.ucfirst($pluralizedEntity).'?.first }
        
        ',0);
    }
}
?>



    // MARK: - Schemas

    /**

    In this func you should :

    #1  Define the Schema
    #2  Register the collections

    */
    override open func configureSchema(){

        // #1  Defines the Schema
        super.configureSchema()

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        $entityDefinition=lcfirst($entity->name).'Definition';
        echoIndentCR('

        let '.$entityDefinition.' = CollectionMetadatum()
        '.$entityDefinition.'.proxy = self.'.lcfirst($pluralizedEntity).'
        // By default we group the observation via the rootObjectUID
        '.$entityDefinition.'.collectionName = '.$entity->name.'.collectionName
        '.$entityDefinition.'.storage = CollectionMetadatum.Storage.monolithicFileStorage
        '.$entityDefinition.'.persistsDistantly = '. (($entity->isDistantPersistencyOfCollectionAllowed())? 'true':'false').'
        '.$entityDefinition.'.inMemory = '. (($entity->shouldPersistsLocallyOnlyInMemory())? 'true':'false').'
        ',0);
    }
}
?>

        // Proceed to configuration
        do{

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        $entityDefinition=lcfirst($entity->name).'Definition';
        echoIndentCR('try self.registryMetadata.configureSchema('.$entityDefinition.')',3);
    }
}
?>

        }catch RegistryError.duplicatedCollectionName(let collectionName){
            bprint("Multiple Attempt to add the Collection named \(collectionName)",file:#file,function:#function,line:#line)
        }catch {
            bprint("\(error)",file:#file,function:#function,line:#line)
        }

        // #2 Registers the collections
        do{
            try self.registerCollections()
        }catch{
        }
    }

    // MARK: - OSX specific

 #if os(OSX) && !USE_EMBEDDED_MODULES

    // MARK: KVO


    override open func observeValue(forKeyPath keyPath: String?, of object: Any?, change: [NSKeyValueChangeKey : Any]?, context: UnsafeMutableRawPointer?) {
        guard context == &_KVOContext else {
            // If the context does not match, this message
            // must be intended for our superclass.
            super.observeValue(forKeyPath: keyPath, of: object, change: change, context: context)
            return
        }
        // We prefer to centralize the KVO for selection indexes at the top level
        if let keyPath = keyPath, let object = object {

        <?php
        foreach ($project->entities as $entity) {
            if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
                $pluralizedEntity=Pluralization::pluralize($entity->name);
                $collectionControllerClassName=ucfirst($pluralizedEntity).'CollectionController';
                $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
                echoIndentCR('
            
            if keyPath=="selectionIndexes" && self.'.$arrayControllerVariableName.' == object as? NSArrayController {
                if let '.lcfirst($pluralizedEntity).' = self.'.$arrayControllerVariableName.'?.selectedObjects as? ['.ucfirst($entity->name).'] {
                     if let selected'.ucfirst($entity->name).' = self.selected'.ucfirst($pluralizedEntity).'{
                        if selected'.ucfirst($entity->name).' == '.lcfirst($pluralizedEntity).'{
                            return // No changes
                        }
                     }
                    self.selected'.ucfirst($pluralizedEntity).'='.lcfirst($pluralizedEntity).'
                }
                return
            }
            ',0);
            }
        }
        ?>
        }

    }

    // MARK:  Delete currently selected items
    <?php
    echoIndentCR('',0);
    foreach ($project->entities as $entity) {
        if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
            $entityName=ucfirst($entity->name);
            $pluralizedEntity=lcfirst(Pluralization::pluralize($entity->name));
            echoIndentCR('
    open func deleteSelected'.ucfirst($pluralizedEntity).'() {
        // you should override this method if you want to cascade the deletion(s)
        if let selected=self.selected'.ucfirst($pluralizedEntity).'{
            for item in selected{
                 self.'.$pluralizedEntity.'.removeObject(item, commit:true)
            }
        }
    }
        ',0);
        }
    }
    ?>

    #else


    #endif

    <?php
    if ($isIncludeInBartlebysCommons){
        echo('
   
    // MARK : new User facility 
    
    /**
    * Creates a new user
    * 
    * you should override this method to customize default (name, email, ...)
    * and call before returning :
    *   if(user.creatorUID != user.UID){
    *       // We don\'t want to add the current user to user list
    *       self.users.add(user, commit:true)
    *   }
    */
    open func newUser() -> User {
        let user=User()
        user.silentGroupedChanges {
            if let creator=self.registryMetadata.currentUser {
                user.creatorUID = creator.UID
            }else{
                // Autopoiesis.
                user.creatorUID = user.UID
            }
            user.spaceUID = self.registryMetadata.spaceUID
            user.document = self // Very important for the  document registry metadata current User
        }
        return user
    }
     
    // MARK: - Synchronization

    // SSE server sent event source
    internal var _sse:EventSource?

    // The EventSource URL for Server Sent Events
    open dynamic lazy var sseURL:URL=URL(string: self.baseURL.absoluteString+"/SSETriggers?spaceUID=\(self.spaceUID)&observationUID=\(self.UID)&lastIndex=\(self.registryMetadata.lastIntegratedTriggerIndex)&runUID=\(Bartleby.runUID)&showDetails=false")!
    
    open var synchronizationHandlers:Handlers=Handlers.withoutCompletion()

    internal var _timer:Timer?

    // MARK: - Local Persistency

    #if os(OSX)


    // MARK:  NSDocument

    // MARK: Serialization
     override open func fileWrapper(ofType typeName: String) throws -> FileWrapper {

        self.registryWillSave()
        let fileWrapper=FileWrapper(directoryWithFileWrappers:[:])
        if var fileWrappers=fileWrapper.fileWrappers {

            // ##############
            // #1 Metadata
            // ##############

            // Try to store a preferred filename
            self.registryMetadata.preferredFileName=self.fileURL?.lastPathComponent
            var metadataData=self.registryMetadata.serialize()

            metadataData = try Bartleby.cryptoDelegate.encryptData(metadataData)

            // Remove the previous metadata
            if let wrapper=fileWrappers[self._metadataFileName] {
                fileWrapper.removeFileWrapper(wrapper)
            }
            let metadataFileWrapper=FileWrapper(regularFileWithContents: metadataData)
            metadataFileWrapper.preferredFilename=self._metadataFileName
            fileWrapper.addFileWrapper(metadataFileWrapper)

            // ##############
            // #2 Collections
            // ##############

            for metadatum: CollectionMetadatum in self.registryMetadata.collectionsMetadata {

                if !metadatum.inMemory {
                    let collectionfileName=self._collectionFileNames(metadatum).crypted
                    // MONOLITHIC STORAGE
                    if metadatum.storage == CollectionMetadatum.Storage.monolithicFileStorage {

                        if let collection = self.collectionByName(metadatum.collectionName) as? CollectibleCollection {

                            // We use multiple files

                            var collectionData = collection.serialize()
                            collectionData = try Bartleby.cryptoDelegate.encryptData(collectionData)

                            // Remove the previous data
                            if let wrapper=fileWrappers[collectionfileName] {
                                fileWrapper.removeFileWrapper(wrapper)
                            }

                            let collectionFileWrapper=FileWrapper(regularFileWithContents: collectionData)
                            collectionFileWrapper.preferredFilename=collectionfileName
                            fileWrapper.addFileWrapper(collectionFileWrapper)
                        } else {
                            // NO COLLECTION
                        }
                    } else {
                        // SQLITE
                    }

                }
            }
        }
        return fileWrapper
    }

    // MARK: Deserialization


    /**
     Standard Bundles loading

     - parameter fileWrapper: the file wrapper
     - parameter typeName:    the type name

     - throws: misc exceptions
     */
    override open func read(from fileWrapper: FileWrapper, ofType typeName: String) throws {
        if let fileWrappers=fileWrapper.fileWrappers {

            // ##############
            // #1 Metadata
            // ##############

            if let wrapper=fileWrappers[_metadataFileName] {
                if var metadataData=wrapper.regularFileContents {
                    metadataData = try Bartleby.cryptoDelegate.decryptData(metadataData)
                    let r = try Bartleby.defaultSerializer.deserialize(metadataData)
                    if let registryMetadata=r as? RegistryMetadata {
                        self.registryMetadata=registryMetadata
                    } else {
                        // There is an error
                        bprint("ERROR \(r)", file: #file, function: #function, line: #line)
                        return
                    }
                    let registryUID=self.registryMetadata.rootObjectUID
                    Bartleby.sharedInstance.replaceRegistryUID(Default.NO_UID, by: registryUID)
                    self.registryMetadata.currentUser?.document=self
                }
            } else {
                // ERROR
            }


            // ##############
            // #2 Collections
            // ##############

            for metadatum in self.registryMetadata.collectionsMetadata {
                // MONOLITHIC STORAGE
                if metadatum.storage == CollectionMetadatum.Storage.monolithicFileStorage {
                    let names=self._collectionFileNames(metadatum)
                    if let wrapper=fileWrappers[names.crypted] ?? fileWrappers[names.notCrypted] {
                        let filename=wrapper.filename
                        if var collectionData=wrapper.regularFileContents {
                            if let proxy=self.collectionByName(metadatum.collectionName) {
                                if let path=filename {
                                    if let ext=path.components(separatedBy: ".").last {
                                        let pathExtension="."+ext
                                        if  pathExtension == Registry.DATA_EXTENSION {
                                            collectionData = try Bartleby.cryptoDelegate.decryptData(collectionData)
                                        }
                                    }
                                  let _ = try proxy.updateData(collectionData,provisionChanges: false)
                                }
                            } else {
                                throw RegistryError.attemptToLoadAnNonSupportedCollection(collectionName:metadatum.d_collectionName)
                            }
                        }
                    } else {
                        // ERROR
                    }
                } else {
                    // SQLite
                }
            }
            do {
                try self._refreshProxies()
            } catch {
                bprint("Proxies refreshing failure \(error)", file: #file, function: #function, line: #line)
            }
           
            DispatchQueue.main.async(execute: {
                self.registryDidLoad()
            })
        }
    }
    
    #else
    
    // MARK: iOS UIDocument serialization / deserialization
    
    // TODO: @bpds(#IOS) UIDocument support
    
    // SAVE content
    override open func contents(forType typeName: String) throws -> Any {
        return ""
    }

    // READ content
    open override func load(fromContents contents: Any, ofType typeName: String?) throws {

    }
    
    #endif  
 
');
}
?>
}