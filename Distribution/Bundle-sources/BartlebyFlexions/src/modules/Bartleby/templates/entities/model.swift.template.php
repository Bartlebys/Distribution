<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = GenerativeHelperForSwift::getCurrentClassNameWithPrefix($d).'.swift';
    // And its package.
    $f->package = 'xOS/models/';
}

// Exclusion

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->name);

if (isset($excludeEntitiesWith)) {
    $exclusion = $excludeEntitiesWith;
}
foreach ($exclusion as $exclusionString) {
    if (strpos($exclusionName, $exclusionString) !== false) {
        return NULL; // We return null
    }
}


if (!defined('_propertyValueString_DEFINED')){
    define("_propertyValueString_DEFINED",true);
    function _propertyValueString(PropertyRepresentation $property){
        if ($property->isObservable===false){
            ////////////////////////////
            // Property isn't observable
            ////////////////////////////
            if(isset($property->default)){
                if($property->type==FlexionsTypes::STRING){
                    return " = \"$property->default\"";
                }else{
                    return " = $property->default";
                }
            }
            return "?";
        }else{
            //////////////////////////
            // Property is observable
            //////////////////////////
        if(isset($property->default)){
            if($property->type==FlexionsTypes::STRING){
                return " = \"$property->default\"{\n 
    willSet { 
       if $property->name != newValue {
            self.provisionChanges() 
       } 
    }
}";
            }else{
                return " = $property->default  {\n 
    willSet { 
       if $property->name != newValue {
            self.provisionChanges() 
       } 
    }
}";
}

        }
        return "? {\n 
    willSet { 
       if $property->name != newValue {
            self.provisionChanges() 
       } 
    }
}";
        }
    }
}


/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
}else{
    echoIndentCR("import Alamofire",0);
    echoIndentCR("import ObjectMapper",0);
    echoIndentCR("import BartlebyKit",0);
}
?>
#endif

// MARK: <?php echo $d->description?>

@objc(<?php echo ucfirst($d->name)?>) public class <?php echo ucfirst($d->name)?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d); ?>{

    // Universal type support
    override public class func typeName() -> String {
        return "<?php echo ucfirst($d->name)?>"
    }

<?php

while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    $dynanicString=($property->isDynamic ? 'dynamic ':'');
    if($property->description!=''){
        echoIndentCR('//' .$property->description. cr(), 1);
    }
    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        echoIndentCR($dynanicString.'public enum ' .$enumTypeName.':'.ucfirst(FlexionsSwiftLang::nativeTypeFor($property->instanceOf)). '{', 1);
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('case ' .ucfirst($element).' = "'.$element.'"', 2);
            }elseif ($property->instanceOf==FlexionsTypes::INTEGER){
                echoIndentCR('case ' .ucfirst($element), 2);
            } else{
                echoIndentCR('case ' .ucfirst($element).' = '.$element, 2);
            }
        }
        echoIndentCR('}', 1);
        echoIndentCR($dynanicString.'public var ' . $name .':'.$enumTypeName._propertyValueString($property), 1);
    }else if($property->type==FlexionsTypes::COLLECTION){
        $instanceOf=FlexionsSwiftLang::nativeTypeFor($property->instanceOf);
        if ($instanceOf==FlexionsTypes::NOT_SUPPORTED){
            $instanceOf=$property->instanceOf;
        }
        echoIndentCR($dynanicString.'public var ' . $name .':['.ucfirst($instanceOf). ']'._propertyValueString($property), 1);
    }else if($property->type==FlexionsTypes::OBJECT){
        echoIndentCR($dynanicString.'public var ' . $name .':'.ucfirst($property->instanceOf)._propertyValueString($property), 1);
    }else{
        $nativeType=FlexionsSwiftLang::nativeTypeFor($property->type);
        if(strpos($nativeType,FlexionsTypes::NOT_SUPPORTED)===false){
            echoIndentCR($dynanicString.'public var ' . $name .':'.$nativeType._propertyValueString($property), 1);
        }else{
            echoIndentCR($dynanicString.'public var ' . $name .':Not_Supported = Not_Supported()//'. ucfirst($property->type), 1);
        }
    }
}

$blockRepresentation=$d;
include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/Mappable.swift.block.php';
if( $modelsShouldConformToNSCoding ) {
    include  FLEXIONS_MODULES_DIR.'Bartleby/templates/blocks/NSSecureCoding.swift.block.php';
}

?>

    required public init() {
        super.init()
    }

    // MARK: Identifiable

    override public class var collectionName:String{
        return "<?php echo lcfirst(Pluralization::pluralize($d->name)) ?>"
    }

    override public var d_collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }


}

<?php /*<- END OF TEMPLATE */?>