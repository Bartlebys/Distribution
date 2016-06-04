<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';
require_once FLEXIONS_MODULES_DIR . 'Bartleby/templates/project/SwiftDocumentConfigurator.php';

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

<?php
if ($isIncludeInBartlebysCommons==false) {
    echoIndentCR('#if !USE_EMBEDDED_MODULES', 0);
    echoIndentCR('import BartlebyKit', 0);
    echoIndentCR('#endif', 0);
}
?>


public class <?php echo($configurator->getClassName())?> : <?php
    if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
        echo('JDocument');
    }else{
        echo('BartlebyDocument');
    }
?> {

    // MARK - Universal Type Support

    override public class func declareCollectibleTypes() {
        super.declareCollectibleTypes()
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

    private var _KVOContext: Int = 0

    // Collection Controller
    // The initial instances are proxies
    // On document deserialization the collection are populated.

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $collectionControllerClassName=ucfirst($pluralizedEntity).'CollectionController';
        $collectionControllerVariableName=lcfirst($pluralizedEntity).'CollectionController';
        echoIndentCR('public var '.lcfirst($pluralizedEntity).'='.$collectionControllerClassName.'()',1);
    }
}
?>

    // MARK: - OSX
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

    public var '.$arrayControllerVariableName.': NSArrayController?{

        willSet{
            // Remove observer on previous array Controller
            '.lcfirst($arrayControllerVariableName).'?.removeObserver(self, forKeyPath: "selectionIndexes", context: &self._KVOContext)
        }
        didSet{
            // Setup the Array Controller in the CollectionController
            self.'.lcfirst($pluralizedEntity).'.arrayController='.lcfirst($arrayControllerVariableName).'
            // Add observer
            '.lcfirst($arrayControllerVariableName).'?.addObserver(self, forKeyPath: "selectionIndexes", options: .New, context: &self._KVOContext)
            if let index=self.registryMetadata.stateDictionary['.$configurator->getClassName().'.kSelected'.ucfirst($entity->name).'IndexKey] as? Int{
               if self.'.lcfirst($pluralizedEntity).'.items.count > index{
                   let selection=self.'.lcfirst($pluralizedEntity).'.items[index]
                   self.'.lcfirst($arrayControllerVariableName).'?.setSelectedObjects([selection])
                }
             }
        }
    }
        ',0);
    }
}
?>


#endif

//Focus indexes persistency

<?php
foreach ($project->entities as $entity) {
    if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $arrayControllerClassName=ucfirst($pluralizedEntity).'ArrayController';
        $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
        echoIndentCR('

    static public let kSelected'.ucfirst($entity->name).'IndexKey="selected'.ucfirst($entity->name).'IndexKey"
    static public let '.strtoupper($entity->name).'_SELECTED_INDEX_CHANGED_NOTIFICATION="'.strtoupper($entity->name).'_SELECTED_INDEX_CHANGED_NOTIFICATION"
    dynamic public var selected'.ucfirst($entity->name).':'.ucfirst($entity->name).'?{
        didSet{
            if let '.lcfirst($entity->name).' = selected'.ucfirst($entity->name).' {
                if let index='.lcfirst($pluralizedEntity).'.items.indexOf('.lcfirst($entity->name).'){
                    self.registryMetadata.stateDictionary['.$configurator->getClassName().'.kSelected'.ucfirst($entity->name).'IndexKey]=index
                     NSNotificationCenter.defaultCenter().postNotificationName('.$configurator->getClassName().'.'.strtoupper($entity->name).'_SELECTED_INDEX_CHANGED_NOTIFICATION, object: nil)

                }
            }
        }
    }
        ',0);
    }
}
?>



    // MARK: - DATA life cycle

    /**

    In this func you should :

    #1  Define the Schema
    #2  Register the collections

    */
    override public func configureSchema(){

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
        '.$entityDefinition.'.observableViaUID = self.registryMetadata.rootObjectUID
        '.$entityDefinition.'.storage = CollectionMetadatum.Storage.MonolithicFileStorage
        '.$entityDefinition.'.allowDistantPersistency = '. (($entity->isDistantPersistencyOfCollectionAllowed())? 'true':'false').'
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

        }catch RegistryError.DuplicatedCollectionName(let collectionName){
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

    override public func observeValueForKeyPath(keyPath: String?, ofObject object: AnyObject?, change: [String : AnyObject]?, context: UnsafeMutablePointer<Void>) {
        guard context == &_KVOContext else {
            // If the context does not match, this message
            // must be intended for our superclass.
            super.observeValueForKeyPath(keyPath, ofObject: object, change: change, context: context)
            return
        }

        // We prefer to centralize the KVO for selection indexes at the top level
        if let keyPath = keyPath, object = object {

        <?php
        foreach ($project->entities as $entity) {
            if ($configurator->collectionControllerShouldBeSupportedForEntity($project,$entity)){
                $pluralizedEntity=Pluralization::pluralize($entity->name);
                $collectionControllerClassName=ucfirst($pluralizedEntity).'CollectionController';
                $arrayControllerVariableName=lcfirst($pluralizedEntity).'ArrayController';
                echoIndentCR('
            
            if keyPath=="selectionIndexes" && self.'.$arrayControllerVariableName.' == object as? NSArrayController {
                if let '.lcfirst($entity->name).'=self.'.$arrayControllerVariableName.'?.selectedObjects.first as? '.ucfirst($entity->name).'{
                    self.selected'.ucfirst($entity->name).'='.lcfirst($entity->name).'
                    return
                }
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
    public func deleteSelected'.$entityName.'() {
        // you should override this method if you want to cascade the deletion(s)
        if let selected=self.selected'.$entityName.'{
            self.'.$pluralizedEntity.'.removeObject(selected)
        }
    }
        ',0);
        }
    }
    ?>

    #else


    #endif
    
    #if os(OSX)

    required public init() {
        super.init()
        <?php echo($configurator->getClassName().'.declareCollectibleTypes()'); ?>
    }
    #else

    public required init(fileURL url: NSURL) {
        super.init(fileURL: url)
        <?php echo($configurator->getClassName().'.declareCollectibleTypes()'); ?>
    }

    #endif

}
