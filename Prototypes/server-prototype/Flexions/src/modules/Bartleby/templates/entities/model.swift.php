<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = GenerativeHelperForSwift::getCurrentClassNameWithPrefix($d).'.swift';
    // And its package.
    $f->package = 'xOS/models/';
}

//////////////////
// EXCLUSIONS
//////////////////

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

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

$isBaseObject = (strpos($d->name,'BartlebyObject')!==false);
$inheritancePrefix = ($isBaseObject ? '' : 'override');
$blockRepresentation=$d;

// BartlebyObject
$baseObjectBlock='';
if($isBaseObject){
    $baseObjectBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/BaseObject.swift.block');
}

// EXPOSED
$exposedBlock='';
if ($modelsShouldConformToExposed){
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>$isBaseObject]);
    $exposedBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Exposed.swift.block.php');
}

// MAPPABLE
$mappableBlock='';
if ($modelsShouldConformToMappable){
    if($isBaseObject){
        $mappableblockEndContent=
            '            if map.mappingType == .toJSON {
                // Define if necessary the UID
                self.defineUID()
            }
            self._typeName <- map[Default.TYPE_NAME_KEY]
            self._id <- map[Default.UID_KEY]'.cr();
    }else{
        $mappableblockEndContent=NULL;
    }
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>$isBaseObject,'mappableblockEndContent'=>$mappableblockEndContent]);
    $mappableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php');
}

// NSSecureCoding
$secureCodingBlock='';
if( $modelsShouldConformToNSSecureCoding ) {
    if($isBaseObject){
        $decodingblockEndContent=
            '            self._typeName=type(of: self).typeName()
            self._id=String(describing: decoder.decodeObject(of: NSString.self, forKey: "_id")! as NSString)
';
        $encodingblockEndContent =
            '        self._typeName=type(of: self).typeName()// Store the universal type name on serialization
        coder.encode(self._typeName, forKey: Default.TYPE_NAME_KEY)
        coder.encode(self._id, forKey: Default.UID_KEY)
        
';
    }else{
        $decodingblockEndContent=NULL;
        $encodingblockEndContent=NULL;
    }
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$d,'isBaseObject'=>$isBaseObject,'decodingblockEndContent'=>$decodingblockEndContent,'encodingblockEndContent'=>$encodingblockEndContent]);
    $secureCodingBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}

$inheritancePrefix = ($isBaseObject ? '' : 'override');
$inversedInheritancePrefix = ($isBaseObject ? 'override':'');
$superInit = ($isBaseObject ? 'super.init()'.cr() : 'super.init()'.cr());

if (!defined('_propertyValueString_DEFINED')){
    define("_propertyValueString_DEFINED",true);
    function _propertyValueString(PropertyRepresentation $property){
        if ($property->isSupervisable===false){

            ////////////////////////////
            // Property isn't supervisable
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
            // Property is supervisable
            //////////////////////////
        if(isset($property->default)){
            if($property->type==FlexionsTypes::STRING){
                return " = \"$property->default\"{
    didSet { 
       if $property->name != oldValue {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue,newValue: $property->name) 
       } 
    }
}";
            }else{
                return " = $property->default  {
    didSet { 
       if $property->name != oldValue {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue".($property->type==FlexionsTypes::ENUM ? ".rawValue" : "" ).",newValue: $property->name".($property->type==FlexionsTypes::ENUM ? ".rawValue" : "" ).")  
       } 
    }
}";
}

        }
        return "? {
    didSet { 
       if $property->name != oldValue {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue".($property->type==FlexionsTypes::ENUM ? "?.rawValue" : "" ).",newValue: $property->name".( $property->type==FlexionsTypes::ENUM ? "?.rawValue" : "" ) .") 
       } 
    }
}";
        }
    }
}

// Include block

$includeBlock='';
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    $includeBlock .= stringIndent("import Alamofire",1);
    $includeBlock .= stringIndent("import ObjectMapper",1);
}else{
    $includeBlock .= stringIndent("import Alamofire",1);
    $includeBlock .= stringIndent("import ObjectMapper",1);
    $includeBlock .= stringIndent("import BartlebyKit",1);
}

//////////////////
// TEMPLATE
//////////////////

include __DIR__.'/model.swift.template.php';
