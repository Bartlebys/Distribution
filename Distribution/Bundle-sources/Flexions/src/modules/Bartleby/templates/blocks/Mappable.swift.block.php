<?php

/*
 *
 * This is a Block template (not a full template)
 * That can be used to generate a NSCoding block in an entity.
 * $blockRepresentation must be set.
 *
 *  usage sample :
 *
 *      if( $modelsShouldConformToNSCoding ) {
 *          $blockRepresentation=$d // ActionRepresentation || EntityRepresentation
 *          include  FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php';
 *      }
 *
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


/* @var $f Flexed */
/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */

/* @var $blockEndContent string */

if (!isset($blockRepresentation)){
    return NULL;
}

?>

    // MARK: Mappable

    required public init?(map: Map) {
        super.init(map:map)
    }

    override open func mapping(map: Map) {
        super.mapping(map: map)
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
                echoIndentCR('self.'.$name . ' <- ( map["' . $name . '"], ISO8601DateTransform() )', 3);
            } else if ($property->type == FlexionsTypes::URL) {
                echoIndentCR('self.'.$name . ' <- ( map["' . $name . '"], URLTransform() )', 3);
            }else if($property->type == FlexionsTypes::STRING ){
                if ($property->isCryptable){
                    echoIndentCR('self.'.$name . ' <- ( map["' . $name . '"], CryptedStringTransform() )', 3);
                }else{
                    echoIndentCR('self.'.$name . ' <- ( map["' . $name . '"] )', 3);
                }
            }else if($property->type == FlexionsTypes::DATA) {
                if ($property->isCryptable) {
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"], CryptedDataTransform() )', 3);
                } else {
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"], DataTransform() )', 3);
                }
            }else if ($property->isGeneratedType){
                if ($property->isCryptable){
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"], CryptedSerializableTransform() )', 3);
                }else {
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"] )', 3);
                }
            }else{
                if ($property->isCryptable){
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"] )// @todo marked generatively as Cryptable Should be crypted!', 3);
                }else {
                    echoIndentCR('self.' . $name . ' <- ( map["' . $name . '"] )', 3);
                }

            }

        }else{
            // RECURSIVE CALL FOR CUSTOMSERIALIZATION
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

if (isset($blockEndContent)){
    echoIndentCR($blockEndContent, 3);
}
if (strpos($blockRepresentation->name,"CollectionController")!==false){
    echo('          if map.mappingType == .fromJSON {
                forEach { $0.collection=self }
            }
');
}
?>
        }
    }

<?php // End of Block ?>