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
 *          include  FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
 *      }
 *
 */

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';


/* @var $f Flexed */
/* @var $blockRepresentation ActionRepresentation || EntityRepresentation */
/* @var $blockEndContent string */

if (!isset($blockRepresentation)){
    return NULL;
}

?>

    // MARK: NSSecureCoding

    required public init?(coder decoder: NSCoder) {
        super.init(coder: decoder)
        self.silentGroupedChanges {
<?php GenerativeHelperForSwift::echoBodyOfInitWithCoder($blockRepresentation, 3);
    if (isset($blockEndContent)){
        echoIndentCR($blockEndContent, 3);
    }
    ?>
        }
    }

    override open func encode(with coder: NSCoder) {
        super.encode(with:coder)
<?php GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($blockRepresentation, 2);?>
    }

    override open class var supportsSecureCoding:Bool{
        return true
    }

<?php // End of Block ?>