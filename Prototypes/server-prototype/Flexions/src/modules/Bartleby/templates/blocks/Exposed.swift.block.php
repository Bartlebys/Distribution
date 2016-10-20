<?php
/**
 *
 * This is a Block template (not a full template)
 * That can be used to generate a the Exposed Protocol block in an entity.
 * $blockRepresentation must be set.
 *
 *  usage sample :
 *
 *  $exposedBlock='';
 *  if ($modelsShouldConformToExposed){
 *      // We define the context for the block
 *      Registry::Instance()->defineVariables(['blockRepresentation'=>$entity,'isBaseObject'=>$isBaseObject]);
 *      $exposedBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Exposed.swift.block.php');
 *  }
 *
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


///////////////////////
// LOCAL REQUIREMENTS
///////////////////////

/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */
/* @var $isBaseObject boolean */

$blockRepresentation=Registry::instance()->valueForKey('blockRepresentation');
$isBaseObject=Registry::Instance()->valueForKey('isBaseObject');
$entityName=$blockRepresentation->concernedType();

if (!isset($blockRepresentation)){
    return NULL;
}

if(!isset($isBaseObject)){
    $isBaseObject=false;
}


////////////////////////
// VARIABLES DEFINITION
////////////////////////

// Compute the Exposed Key String

$inheritancePrefix = ($isBaseObject ? '' : 'override');
if($isBaseObject){
    $exposedKeysStringDeclaration='var exposed=[String]()'.cr();
    $defaultGetString="throw ObjectExpositionError.UnknownKey(key: key,forTypeName: $entityName.typeName())".cr();
    $defaultSetString="throw ObjectExpositionError.UnknownKey(key: key,forTypeName: $entityName.typeName())".cr();
}else{
    $exposedKeysStringDeclaration='var exposed=super.exposedKeys'.cr();
    $defaultGetString='return try super.getExposedValueForKey(key)'.cr();
    $defaultSetString='return try super.setExposedValue(value, forKey: key)'.cr();
}

$onePropertyHasBeenAdded=false;
$exposedKeysString='[';
$setterSwitch='';
$getterSwitch='';
// Exposed support for entities and parameters classes.
// $d may be ActionRepresentation or EntityRepresentation
$isEntity=($blockRepresentation instanceof EntityRepresentation);
while ($isEntity?$blockRepresentation->iterateOnProperties():$blockRepresentation->iterateOnParameters() === true) {
    /* @var $property PropertyRepresentation */
    $property = $isEntity?$blockRepresentation->getProperty():$blockRepresentation->getParameter();
    $name = $property->name;
    if ($property->mutability==Mutability::IS_VARIABLE){
        if ($property->mutability==Mutability::IS_VARIABLE){
            if($property->scope==Scope::IS_PUBLIC){

                if($property->type==FlexionsTypes::ENUM){
                    $typeName=$property->emumPreciseType;
                }else if($property->type==FlexionsTypes::COLLECTION){
                    $instanceOf=FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
                    if ($instanceOf==FlexionsTypes::NOT_SUPPORTED){
                        $instanceOf=$property->instanceOf;
                    }
                    $typeName='['.ucfirst($instanceOf). ']';
                }else if($property->type==FlexionsTypes::OBJECT){
                    $typeName=ucfirst($property->instanceOf);
                }else{
                    $typeName=FlexionsSwiftLang::nativeTypeFor($property->type);
                }

                if(!$onePropertyHasBeenAdded){
                    $onePropertyHasBeenAdded=true;
                    $exposedKeysString.='"'.$name.'"';
                }else{
                    $exposedKeysString.=',"'.$name.'"';
                }
                $setterSwitch.='
            case "'.$property->name.'":
                if let casted=value as? '.$typeName.'{
                    self.'.$property->name.'=casted
                }';
                $getterSwitch.='
            case "'.$property->name.'":
               return self.'.$property->name;
            }
        }
    }
}
$exposedKeysString.=']';
$getterSwitch.=cr();
$setterSwitch.=cr();


////////////////////////
// BLOCK TEMPLATE LOGIC
////////////////////////


?>

    // MARK: - Exposed (Bartleby's KVC like generative implementation)

    /// Return all the exposed instance variables keys. (Exposed == public and modifiable).
    <?php echo $inheritancePrefix ?> open var exposedKeys:[String] {
        <?php echo $exposedKeysStringDeclaration ?>
        exposed.append(contentsOf:<?php echo $exposedKeysString ?>)
        return exposed
    }


    /// Set the value of the given key
    ///
    /// - parameter value: the value
    /// - parameter key:   the key
    ///
    /// - throws: throws an Exception when the key is not exposed
    <?php echo $inheritancePrefix ?> open func setExposedValue(_ value:Any?, forKey key: String) throws {
        switch key {<?php echo $setterSwitch ?>
            default:
                <?php echo $defaultSetString ?>
        }
    }


    /// Returns the value of an exposed key.
    ///
    /// - parameter key: the key
    ///
    /// - throws: throws Exception when the key is not exposed
    ///
    /// - returns: returns the value
    <?php echo $inheritancePrefix ?> open func getExposedValueForKey(_ key:String) throws -> Any?{
        switch key {<?php echo $getterSwitch ?>
            default:
                <?php echo $defaultGetString ?>
        }
    }