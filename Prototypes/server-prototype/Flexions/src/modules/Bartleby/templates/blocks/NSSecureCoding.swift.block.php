<?php
/**
 *
 * This is a Block template (not a full template)
 * That can be used to generate a NSCoding block in an entity.
 * $blockRepresentation must be set.
 *
 *  Usage sample :
 *
 *  $secureCodingBlock='';
 *  if($modelsShouldConformToNSSecureCoding) {
 *      // We define the context for the block
 *      Registry::Instance()->defineVariables(['blockRepresentation'=>$virtualEntity,'isBaseObject'=>false,'decodingblockEndContent'=>'','encodingblockEndContent'=>'']);
 *      $secureCodingBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
 *  }
 *
 */

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';


///////////////////////
// LOCAL REQUIREMENTS
///////////////////////

/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */
/* @var $decodingblockEndContent string */
/* @var $encodingblockEndContent string */
/* @var $isBaseObject boolean */

$blockRepresentation=Registry::instance()->valueForKey('blockRepresentation');
$decodingblockEndContent=Registry::instance()->valueForKey('decodingblockEndContent');
$encodingblockEndContent=Registry::instance()->valueForKey('encodingblockEndContent');
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

    // MARK: - NSSecureCoding

    required public init?(coder decoder: NSCoder) {
        <?php if(!$isBaseObject){echo'super.init(coder: decoder)'.cr();}else{echo'super.init()'.cr();} ?>
        self.silentGroupedChanges {
<?php GenerativeHelperForSwift::echoBodyOfInitWithCoder($blockRepresentation, 3);
    if (isset($decodingblockEndContent)){
        echo $decodingblockEndContent;
    }
    ?>
        }
    }

    <?php echo $inheritancePrefix?> open func encode(with coder: NSCoder) {
        <?php if(!$isBaseObject){echo'super.encode(with:coder)'.cr();}else{echo cr();} ?>
<?php GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($blockRepresentation, 2);
        if (isset($encodingblockEndContent)){
            echo $encodingblockEndContent;
        }
?>
    }

    <?php echo $inheritancePrefix?> open class var supportsSecureCoding:Bool{
        return true
    }

<?php // End of Block ?>