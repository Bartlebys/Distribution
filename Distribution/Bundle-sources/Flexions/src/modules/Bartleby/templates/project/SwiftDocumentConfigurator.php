<?php
class SwiftDocumentConfigurator{

    /**
     * @var string The file name to be used
     */
    public $filename;// E.g = WorkspaceDocument.swift

    /**
     * @var array the array of the actions to be used.
     */
    public $includeCollectionControllerForEntityContainingString=array();

    /**
     * @var arrays the array of action name to explicitly exclude
     */
    public $excludeCollectionControllerForEntityContainingString=Array();

    function getClassName(){
        return str_replace('.swift','',$this->filename);
    }

    function collectionControllerShouldBeSupportedForEntity(ProjectRepresentation $project, EntityRepresentation $entity){
        $inclusionName = strtolower(str_replace($project->classPrefix, '', $entity->name));
        foreach ($this->excludeCollectionControllerForEntityContainingString as $exclusion){
            if ($entity->name===$exclusion){
                return false;
            }
        }
        foreach ($this->includeCollectionControllerForEntityContainingString as $inclusion) {
            if (!(strpos($inclusionName, strtolower($inclusion)) === false)){
                return true;
            }
        }
        return false;
    }

}
