<?php

require_once __DIR__ . '/BJMParser.php';

class PathsFlexer  extends BJMParser {

    /**
     * ProjectFlexer constructor.
     * @param MetaFlexer $appMetaFlexer
     */
    public function __construct(MetaFlexer $appMetaFlexer) {
        parent::__construct($appMetaFlexer);
    }

    /**
     * Generates a paths array
     * @param ProjectRepresentation $project
     * @param string $templatePath
     * @return array of JSM modeled paths.
     */
    public function generateFromRepresentation(ProjectRepresentation $project, $templatePath) {
        $modelsShouldConformToNSSecureCoding = true;
        $d = $project;
        ob_start();
        include $templatePath;
        $json = ob_get_clean();
        // transform $json to an array of JSM modeled paths
        $arrayOfPaths=json_decode($json,true);
        if (!is_array($arrayOfPaths)){
            throw new Exception('Decoded paths are not a valid associative array');
        }
        if (array_key_exists(BJM_PATHS,$arrayOfPaths)){
            return $arrayOfPaths[BJM_PATHS];
        }else{
            throw new Exception(BJM_PATHS.' key is not defined');
        }
    }

}