<?php

require_once __DIR__ . '/BJMParser.php';

class ActionFlexer extends BJMParser {

    /**
     * ActionFlexer constructor.
     * @param MetaFlexer $appMetaFlexer
     */
    public function __construct(MetaFlexer $appMetaFlexer) {
        parent::__construct($appMetaFlexer);
    }

    /**
     * Generates a Flexed source based on a project a template path and a destination folder
     * @param ActionRepresentation $project
     * @param string $templatePath
     * @param string $destinationFolder
     * @return Flexed
     */
    public function generateFromRepresentation(ActionRepresentation $action, $templatePath, $destinationFolder) {
        $modelsShouldConformToNSCoding = true;
        $d = $action;
        $f = new Flexed();
        $f->author = $this->_metaFlexer->author;
        $f->projectName = $this->_metaFlexer->projectName;
        $f->company = $this->_metaFlexer->company;
        $f->year = $this->_metaFlexer->year;
        ob_start();
        include $templatePath;
        $result = ob_get_clean();
        if ($f->fileName != null) {
            $f->source = $result; // We store the generation result
            $f->packagePath = $destinationFolder . '/' . $f->package;//and the package path
            $this->_metaFlexer->log('+Adding ' . $f->fileName);
        } else {
            $this->_metaFlexer->log('fileName or package is not defined in ' . $templatePath);
        }

        return $f;
    }
}