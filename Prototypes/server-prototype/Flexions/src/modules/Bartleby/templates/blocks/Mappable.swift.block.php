<?php
/**
 *
 * This is a Block template (not a full template)
 * That can be used to generate a Mappable Protocol block in an entity.
 * $blockRepresentation must be set.
 *
 *  Usage sample :
 *
 * $mappableBlock='';
 * if ($modelsShouldConformToMappable){
 *      // We define the context for the block
 *      Registry::Instance()->defineVariables(['blockRepresentation'=>$virtualEntity,'isBaseObject'=>false,'mappableblockEndContent'=>'']);
 *     $mappableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php');
 * }
 *
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';

///////////////////////
// LOCAL REQUIREMENTS
///////////////////////

/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */
/* @var $mappableblockEndContent string */
/* @var $isBaseObject boolean */

$blockRepresentation=Registry::instance()->valueForKey('blockRepresentation');
$mappableblockEndContent=Registry::instance()->valueForKey('mappableblockEndContent');
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

$inheritancePrefix = ($isBaseObject ? '' : 'override');


////////////////////////
// BLOCK TEMPLATE LOGIC
////////////////////////

?>

    // MARK: - Mappable

    required public init?(map: Map) {
        <?php if(!$isBaseObject){echo'super.init(map:map)'.cr();}else{echo cr();} ?>
    }

    <?php echo $inheritancePrefix?> open func mapping(map: Map) {
        <?php if(!$isBaseObject){echo'super.mapping(map: map)'.cr();}else{ echo cr();} ?>
        self.silentGroupedChanges {
<?php

// We use includes so we need to declare the functions once
if (!defined('MAPPABLE_BLOCK')){
    define('MAPPABLE_BLOCK',true);
    /* @var $property PropertyRepresentation */
    function mappable_block_property_Loop($property){
        $name = $property->name;
        if ($property->isSerializable==false){
            return;
        }
        if (!isset($property->customSerializationMapping)){
            // STANDARD MAPPING
            if ($property->type == FlexionsTypes::DATETIME){
                echoIndent('self.'.$name . ' <- ( map["' . $name . '"], ISO8601DateTransform() )', 3);
            } else if ($property->type == FlexionsTypes::URL) {
                echoIndent('self.'.$name . ' <- ( map["' . $name . '"], URLTransform() )', 3);
            }else if($property->type == FlexionsTypes::STRING ){
                if ($property->isCryptable){
                    echoIndent('self.'.$name . ' <- ( map["' . $name . '"], CryptedStringTransform() )', 3);
                }else{
                    echoIndent('self.'.$name . ' <- ( map["' . $name . '"] )', 3);
                }
            }else if($property->type == FlexionsTypes::DATA) {
                if ($property->isCryptable) {
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"], CryptedDataTransform() )', 3);
                } else {
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"], DataTransform() )', 3);
                }
            }else if ($property->isGeneratedType){
                if ($property->isCryptable){
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"], CryptedSerializableTransform() )', 3);
                }else {
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"] )', 3);
                }
            }else{
                if ($property->isCryptable){
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"] )// @todo marked generatively as Cryptable Should be crypted!', 3);
                }else {
                    echoIndent('self.' . $name . ' <- ( map["' . $name . '"] )', 3);
                }
            }
        }else{
            // RECURSIVE CALL FOR CUSTOM SERIALIZATION
            foreach ($property->customSerializationMapping as $property) {
                mappable_block_property_Loop($property);
            }
        }
    }
}

// Mappable support for entities and parameters classes.
// $d may be ActionRepresentation or EntityRepresentation
$isEntity=($blockRepresentation instanceof EntityRepresentation);
while ($isEntity?$blockRepresentation->iterateOnProperties():$blockRepresentation->iterateOnParameters() === true) {
    /* @var $property PropertyRepresentation */
    $property = $isEntity?$blockRepresentation->getProperty():$blockRepresentation->getParameter();
    mappable_block_property_Loop($property);
}

if (isset($mappableblockEndContent)){
    echo($mappableblockEndContent);
}
?>
        }
    }

<?php // End of Block ?>