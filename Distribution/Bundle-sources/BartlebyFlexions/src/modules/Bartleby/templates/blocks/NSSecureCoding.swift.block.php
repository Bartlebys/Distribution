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
<?php GenerativeHelperForSwift::echoBodyOfInitWithCoder($blockRepresentation, 2);
if (isset($blockEndContent)){
    echoIndentCR($blockEndContent, 2);
}
?>

    }

    override public func encodeWithCoder(coder: NSCoder) {
        super.encodeWithCoder(coder)
<?php GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($blockRepresentation, 2);?>
    }


    override public class func supportsSecureCoding() -> Bool{
        return true
    }

<?php // End of Block ?>