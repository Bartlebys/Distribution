<?php

class SwiftDocumentConfigurator{

    /**
     * @var string The file name to be used
     */
    public $filename;// E.g = WorkspaceDocument.swift

    /**
     * @var array the array of the actions to be used.
     */
    public $useActionsContainingString=array();

    function getClassName(){
        return str_replace('.swift','',$this->filename);
    }

    function actionsShouldBeSupportedForEntity(ProjectRepresentation $project, EntityRepresentation $entity){
        $inclusionName = strtolower(str_replace($project->classPrefix, '', $entity->name));
        foreach ($this->useActionsContainingString as $inclusion) {
            if (!(strpos($inclusionName, strtolower($inclusion)) === false)){
                return true;
            }
        }
        return false;
    }

}
