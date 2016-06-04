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

@objc(<?php echo $collectionControllerClass ?>) public class <?php echo $collectionControllerClass ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$entityRepresentation); ?>,IterableCollectibleCollection{

    // Universal type support
    override public class func typeName() -> String {
        return "<?php echo $collectionControllerClass ?>"
    }

    weak public var undoManager:NSUndoManager?

    public var spaceUID:String=Default.NO_UID

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

    /**
    An iterator that permit dynamic approaches.
    The Registry ignore the real types.
    Currently we do not use SequenceType, Subscript, ...

    - parameter on: the closure
    */
    public func superIterate(@noescape on:(element: protocol<Collectible,Supervisable>)->()){
        for item in self.items {
            on(element:item)
        }
    }

<?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {

   if ($entityRepresentation->groupedOnCommit()){
       echo('
    /**
    Commit all the changes in one bunch
    Marking commit on each item will toggle hasChanged flag.
    */
    public func commitChanges() -> [String] {
        var UIDS=[String]()
        let changedItems=self.items.filter { $0.toBeCommitted == true }
        bprint("\(changedItems.count) \( changedItems.count>1 ? "'.lcfirst(Pluralization::pluralize($entityRepresentation->name)).'" : "'.lcfirst($entityRepresentation->name).'" )  has changed in '.$collectionControllerClass.'",file:#file,function:#function,line:#line,category: Default.BPRINT_CATEGORY)
        if  changedItems.count > 0 {
            UIDS.append(changed.UID)
            Update' . ucfirst(Pluralization::pluralize($entityRepresentation->name)) . '.commit(changedItems, inDataSpace:self.spaceUID)
        }
        return UIDS
    }
');
   }else{
       echo('
    /**
    Commit all the changes in one bunch
    Marking commit on each item will toggle hasChanged flag.
    */
    public func commitChanges() -> [String] {
        var UIDS=[String]()
        let changedItems=self.items.filter { $0.toBeCommitted == true }
        bprint("\(changedItems.count) \( changedItems.count>1 ? "'.lcfirst(Pluralization::pluralize($entityRepresentation->name)).'" : "'.lcfirst($entityRepresentation->name).'" )  has changed in '.$collectionControllerClass.'",file:#file,function:#function,line:#line,category: Default.BPRINT_CATEGORY)
        for changed in changedItems{
            UIDS.append(changed.UID)
            Update' . ucfirst($entityRepresentation->name) . '.commit(changed, inDataSpace:self.spaceUID)
        }
        return UIDS
    }
');
   }




}else{
echo('
    /**
    Those item are not committable.
    */
    public func commitChanges() ->[String] { return [String]() }
    
');
}
?>

    required public init() {
        super.init()
    }


    dynamic public var items:[<?php echo ucfirst($entityRepresentation->name)?>]=[<?php echo ucfirst($entityRepresentation->name)?>]()


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

$blockEndContent="";// This is injected in the block
include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/Mappable.swift.block.php';
if( $modelsShouldConformToNSCoding ) {
    include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
}?>

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

<?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()){
    echo("
            if item.committed==false{
               Create$entityRepresentation->name.commit(item, inDataSpace:self.spaceUID)
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

        <?php if ($entityRepresentation->isDistantPersistencyOfCollectionAllowed()) {
            echo('
            Delete'.$entityRepresentation->name.'.commit(item.UID,fromDataSpace:self.spaceUID)  '.cr());
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

    
}