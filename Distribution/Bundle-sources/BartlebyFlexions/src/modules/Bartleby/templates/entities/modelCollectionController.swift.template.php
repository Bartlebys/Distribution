<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation*/
/* @var $h Hypotypose */


if (isset( $f,$d,$h)) {


    // We use explicit name (!)
    // And reserve $f , $d , $h possibly for blocks

    /* @var $flexed Flexed*/
    /* @var $entityRepresentation EntityRepresentation*/
    /* @var $hypotypose Hypotypose*/

    $flexed=$f;
    $entityRepresentation=$d;
    $hypotypose=$h;

    // We determine the file name.
    $f->fileName = ucfirst(Pluralization::pluralize($d->name)) . 'CollectionController.swift';
    // And its package.
    $f->package = 'xOS/collectionControllers/';
    
}else{
    return NULL;
}




// Exclusion -

//Collection controllers are related to actions.

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $entityRepresentation->name);

$includeCollectionController = false;
if (isset($xOSIncludeCollectionControllerForEntityNamed)) {
    foreach ($xOSIncludeCollectionControllerForEntityNamed as $inclusion) {
        if (strpos($exclusionName, $inclusion) !== false) {
            $includeCollectionController = true;
        }

    }
    if (!$includeCollectionController) {
        if (isset($excludeActionsWith)) {
            $exclusion = $excludeActionsWith;
        }
        foreach ($exclusion as $exclusionString) {
            if (strpos($exclusionName, $exclusionString) !== false) {
                return NULL; // We return null
            }
        }
    }
}


$collectionControllerClass=ucfirst(Pluralization::pluralize($entityRepresentation->name)).'CollectionController';

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$entityRepresentation); ?>

import Foundation
#if os(OSX)
import AppKit
#endif
#if !USE_EMBEDDED_MODULES
<?php
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    echoIndent(cr(),0);
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
}else{
    echoIndentCR(cr(),0);
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
    echoIndentCR("import BartlebyKit",0);
}
?>
#endif

// MARK: A  collection controller of "<?php echo lcfirst(Pluralization::pluralize($entityRepresentation->name)); ?>"

// This controller implements data automation features.
// it uses KVO , KVC , dynamic invocation, oS X cocoa bindings,...
// It should be used on documents and not very large collections as it is computationnally intensive

@objc(<?php echo $collectionControllerClass ?>) public class <?php echo $collectionControllerClass ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$entityRepresentation); ?>,IterableCollectibleCollection{

    weak public var undoManager:NSUndoManager?

    public var spaceUID:String=Default.NO_UID

    public var observableByUID:String=Default.NOT_OBSERVABLE

    #if os(OSX) && !USE_EMBEDDED_MODULES

    public weak var arrayController:NSArrayController?

    #endif

    weak public var tableView: BXTableView?


    public func generate() -> AnyGenerator<<?php echo ucfirst($entityRepresentation->name)?>> {
        var nextIndex = -1
        let limit=self.items.count-1
        return AnyGenerator {
            nextIndex += 1
            if (nextIndex > limit) {
                return nil
            }
            return self.items[nextIndex]
        }
    }

    required public init() {
        super.init()
    }

    deinit{
        _stopObservingAllItems()
    }

    dynamic public var items:[<?php echo ucfirst($entityRepresentation->name)?>]=[<?php echo ucfirst($entityRepresentation->name)?>]()

    // We store the UIDs to guarantee KVO consistency.
    // Example : calling Mapper().toJSON(self) on a Collection adds the items to KVO.
    // Calling twice would add twice the observers.
    private var _observedUIDS=[String]()


    private func _stopObservingAllItems(){
        for item in items {
            _stopObserving(item)
        }
    }

    private func _startObservingAllItems(){
        for item in items {
            _startObserving(item)
        }
    }




// MARK: Identifiable

    override public class var collectionName:String{
        return <?php echo ucfirst($entityRepresentation->name)?>.collectionName
    }

    override public var d_collectionName:String{
        return <?php echo ucfirst($entityRepresentation->name)?>.collectionName
    }



<?php


    // We just want to inject an item property Items
    $virtualEntity=new EntityRepresentation();
    $itemsProperty=new PropertyRepresentation();
    $itemsProperty->name="items";
    $itemsProperty->type=FlexionsTypes::COLLECTION;
    $itemsProperty->instanceOf=ucfirst($entityRepresentation->name);
    $itemsProperty->required=true;
    $itemsProperty->isDynamic=true;
    $itemsProperty->default=NULL;
    $itemsProperty->isGeneratedType=true;

    $virtualEntity->properties[]=$itemsProperty;
    $blockRepresentation=$virtualEntity;

$blockEndContent="_startObservingAllItems()";// This is injected in the block
include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/Mappable.swift.block.php';
if( $modelsShouldConformToNSCoding ) {
    include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
}
?>

    // MARK: Add

    public func add(item:Collectible){
        #if os(OSX) && !USE_EMBEDDED_MODULES
        if let arrayController = self.arrayController{
            self.insertObject(item, inItemsAtIndex: arrayController.arrangedObjects.count)
        }else{
            self.insertObject(item, inItemsAtIndex: items.count)
        }
        #else
        self.insertObject(item, inItemsAtIndex: items.count)
        #endif
    }

    // MARK: Insert

    public func insertObject(item: Collectible, inItemsAtIndex index: Int) {
        if let item=item as? <?php echo ucfirst($entityRepresentation->name)?>{

<?php if ($entityRepresentation->isUndoable()) {
    echo('
            if let undoManager = self.undoManager{
                // Has an edit occurred already in this event?
                if undoManager.groupingLevel > 0 {
                    // Close the last group
                    undoManager.endUndoGrouping()
                    // Open a new group
                    undoManager.beginUndoGrouping()
                }
            }

            // Add the inverse of this invocation to the undo stack
            if let undoManager: NSUndoManager = undoManager {
                undoManager.prepareWithInvocationTarget(self).removeObjectFromItemsAtIndex(index)
                if !undoManager.undoing {
                    undoManager.setActionName(NSLocalizedString("Add' . ucfirst($entityRepresentation->name) . '", comment: "Add' . ucfirst($entityRepresentation->name) . ' undo action"))
                }
            }
            ');
}
?>

            #if os(OSX) && !USE_EMBEDDED_MODULES
            if let arrayController = self.arrayController{
                // Add it to the array controller's content array
                arrayController.insertObject(item, atArrangedObjectIndex:index)

                // Re-sort (in case the use has sorted a column)
                arrayController.rearrangeObjects()

                // Get the sorted array
                let sorted = arrayController.arrangedObjects as! [<?php echo ucfirst($entityRepresentation->name)?>]

                if let tableView = self.tableView{
                    // Find the object just added
                    let row = sorted.indexOf(item)!
                    // Begin the edit in the first column
                    tableView.editColumn(0, row: row, withEvent: nil, select: true)
                 }

            }else{
                // Add directly to the collection
                self.items.insert(item, atIndex: index)
            }
            #else
                self.items.insert(item, atIndex: index)
            #endif

            self._startObserving(item)

<?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
    echo("
            if item.committed==false{
               Create$entityRepresentation->name.commit(item, inDataSpace:self.spaceUID, observableBy: self.observableByUID)
            }".cr());
}
?>

        }else{
           
        }
    }




    // MARK: Remove

    public func removeObjectFromItemsAtIndex(index: Int) {
        if let item : <?php echo ucfirst($entityRepresentation->name)?> = items[index] {
<?php if ($entityRepresentation->isUndoable()) {
    echo(
'
            // Add the inverse of this invocation to the undo stack
            if let undoManager: NSUndoManager = undoManager {
                // We don\'t want to introduce a retain cycle
                // But with the objc magic casting undoManager.prepareWithInvocationTarget(self) as? UsersCollectionController fails
                // That\'s why we have added an registerUndo extension on NSUndoManager
                undoManager.registerUndo({ () -> Void in
                   self.insertObject(item, inItemsAtIndex: index)
                })
                if !undoManager.undoing {
                    undoManager.setActionName(NSLocalizedString("Remove' . ucfirst($entityRepresentation->name) . '", comment: "Remove ' . ucfirst($entityRepresentation->name) . ' undo action"))
                }
            }
            ');
}
?>

            // Unregister the item
            Registry.unRegister(item)

            //Update the commit flag
            item.committed=false
            #if os(OSX) && !USE_EMBEDDED_MODULES
            // Remove the item from the array
            if let arrayController = self.arrayController{
                arrayController.removeObjectAtArrangedObjectIndex(index)
            }else{
                items.removeAtIndex(index)
            }
            #else
            items.removeAtIndex(index)
            #endif

            self._stopObserving(item)

        <?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {
            echo('
            Delete'.$entityRepresentation->name.'.commit(item.UID,fromDataSpace:self.spaceUID, observableBy: self.observableByUID)  '.cr());
        }
        ?>


        }
    }

    public func removeObject(item: Collectible)->Bool{
        var index=0
        for storedItem in items{
            if item.UID==storedItem.UID{
                self.removeObjectFromItemsAtIndex(index)
                return true
            }
            index += 1
        }
        return false
    }

    public func removeObjectWithID(id:String)->Bool{
        var index=0
        for storedItem in items{
            if id==storedItem.UID{
                self.removeObjectFromItemsAtIndex(index)
                return true
            }
            index += 1
        }
        return false
    }


    // MARK: - Key Value Observing

    private var KVOContext: Int = 0

    private func _startObserving(item: <?php echo ucfirst($entityRepresentation->name)?>) {
        if _observedUIDS.indexOf(item.UID) == nil {
            _observedUIDS.append(item.UID)
<?php
while ( $entityRepresentation ->iterateOnProperties() === true ) {
    $property = $entityRepresentation->getProperty();
    $name = $property->name;
    echoIndentCR('item.addObserver(self, forKeyPath: "'.$name.'", options: .Old, context: &KVOContext)',3);
} ?>
        }
    }

    private func _stopObserving(item: <?php echo ucfirst($entityRepresentation->name)?>) {
        if let idx=_observedUIDS.indexOf(item.UID)  {
            _observedUIDS.removeAtIndex(idx)
<?php
while ( $entityRepresentation ->iterateOnProperties() === true ) {
    $property = $entityRepresentation->getProperty();
    $name = $property->name;
    echoIndentCR('item.removeObserver(self, forKeyPath: "'.$name.'", context: &KVOContext)',3);
} ?>
        }
    }

    override public func observeValueForKeyPath(keyPath: String?, ofObject object: AnyObject?, change: [String : AnyObject]?, context: UnsafeMutablePointer<Void>) {
        guard context == &KVOContext else {
        // If the context does not match, this message
        // must be intended for our superclass.
        super.observeValueForKeyPath(keyPath, ofObject: object, change: change, context: context)
            return
        }
        <?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {
            echo('
        if let '.lcfirst($entityRepresentation->name).' = object as? '.ucfirst($entityRepresentation->name).'{
            Update'.$entityRepresentation->name.'.commit('.lcfirst($entityRepresentation->name).', inDataSpace:self.spaceUID, observableBy: self.observableByUID)
        }');
        }?>

        if let undoManager = self.undoManager{

            if let keyPath = keyPath, object = object, change = change {
                var oldValue: AnyObject? = change[NSKeyValueChangeOldKey]
                 if oldValue is NSNull {
                    oldValue = nil
                }
                undoManager.prepareWithInvocationTarget(object).setValue(oldValue, forKeyPath: keyPath)
            }
        }
        #if os(OSX) && !USE_EMBEDDED_MODULES
        // Sort descriptors support
        if let keyPath = keyPath {
            if let arrayController = self.arrayController{
                for sortDescriptor:NSSortDescriptor in arrayController.sortDescriptors{
                    if sortDescriptor.key==keyPath {
                        // Re-sort
                        arrayController.rearrangeObjects()
                    }
                }
            }
        }
        #endif
    }
}