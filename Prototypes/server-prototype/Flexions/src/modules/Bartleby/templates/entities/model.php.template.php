<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $classNameWithoutPrefix=ucfirst(substr($d->name,strlen($h->classPrefix)));
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'_generated/Models/';
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



/* TEMPLATES STARTS HERE -> */?><?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>
namespace Bartleby\Models;

require_once BARTLEBY_ROOT_FOLDER.'Core/Model.php';
require_once BARTLEBY_PUBLIC_FOLDER . 'Configuration.php';

use Bartleby\Core\Model;
<?php
$hasBeenImported=array();
while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    if($property->isGeneratedType){
        $className=$property->instanceOf;
        $className=$h->ucFirstRemovePrefixFromString($className);

        /*
            Bartleby swift uses Alias<T> to resolve typed external reference
         */

        $genericMarker='\<';
        $genericRes=preg_replace('/<(.*)>/','$0',$className);
        if (preg_match('/^(.*)'.$genericMarker.'/', $className, $matches)) {
            $className = $matches[1];
        }
        $notGenerated=array('Alias');
        if (! in_array($className,$hasBeenImported)) {
            if (in_array($className,$notGenerated)){
                echoIndent('require_once BARTLEBY_ROOT_FOLDER.\'/Commons/Models/'.$className.'.php\';',0);
            }else{
                if ($className!=='Dictionary'){
                    echoIndent('require_once __DIR__.\'/'.$className.'.php\';',0);
                }
            }
            echoIndent('use Bartleby\Models\\'.$className.';//'.$genericRes,0);
            $hasBeenImported[]=$className;
        }

    }
} ?>

class <?php echo $classNameWithoutPrefix?> extends Model{

<?php
/* @var $property PropertyRepresentation */

// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name=$property->name;
    $typeOfProp=$property->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $typeOfProp = $h->ucFirstRemovePrefixFromString($property->instanceOf);
        if($typeOfProp==$c){
            $typeOfProp=' array of '.$typeOfProp;
        }
    }
    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        $typeOfProp=$property->instanceOf.' '.$typeOfProp;
        echoIndent('// Enumeration of possibles values of '.$name, 1);
        $enumCounter=0;
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndent('const ' .$enumTypeName.'_'.ucfirst($element).' = "'.$element.'";' ,1);
            }else if($property->instanceOf==FlexionsTypes::INTEGER){
                echoIndent('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$enumCounter.';', 1);
                $enumCounter++;
            }else{
                echoIndent('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$element.';', 1);
            }
        }
    }
    echoIndent('/* @var '.$typeOfProp.' '.$property->description.' */',1);
    if($d->firstProperty()){
        echoIndent('public $'.$name.';',1);
    }else if ($d->lastProperty()){
        echoIndent('public $'.$name.';',1);
    }else{
        echoIndent('public $'.$name.';',1);
    };
    echoIndent('',0);

    if($d->lastProperty()){
        echoIndent(cr(),0);
    }
}
?>


    function classMapping(array $mapping=array()){
<?php while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    $typeOfProp=$property->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $type = $property->instanceOf;
        if ($property->isGeneratedType) {
            $type = $h->ucFirstRemovePrefixFromString($type);
        }
        if ($property->type == FlexionsTypes::COLLECTION) {
            echoIndent('$mapping[\'' . $property->name . '\']=array(\'' . $type . '\');', 2);
        } else {
            echoIndent('$mapping[\'' . $property->name . '\']=\'' . $type . '\';', 2);
        }
    }


}?>
        return parent::classMapping($mapping);
    }

}

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>