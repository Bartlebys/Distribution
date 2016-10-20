<?php

require_once __DIR__ . '/BJMParser.php';

/**
 * The entity flexer can be used in global app generation context
 * or discreetly to generate one entity.
 * Class EntityFlexer
 */
class EntityFlexer extends BJMParser {

    /**
     * EntityFlexer constructor.
     * @param MetaFlexer $metaFlexer
     */
    public function __construct(MetaFlexer $metaFlexer) {
        parent::__construct($metaFlexer);
    }

    /**
     * Generates a Flexed entity based on en Entity JSON descriptor and a Template path
     * it can be used without more context to generate a punctual entity class.
     * @param string $descriptorFilePath
     * @param string $templatePath
     * @param string $destinationFolder
     * @return Flexed
     */
    public function generateFromDescriptor($descriptorFilePath, $templatePath, $destinationFolder) {
        $entity = $this->jsonToEntityRepresentation($descriptorFilePath);
        $flexed=$this->generateFromRepresentation($entity,$templatePath,$destinationFolder);
        return $flexed;
    }


    /**
     * Generates a Flexed entity based on it Representation and a Template path
     * it can be used without more context.
     * @param string $templatePath
     * @param string $destinationFolder
     * @return Flexed
     */
    public function generateFromRepresentation(EntityRepresentation $entity, $templatePath, $destinationFolder) {
        $modelsShouldConformToNSSecureCoding = true;
        $d = $entity;
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
            $this->_metaFlexer->log('+Adding ' . $f->fileName, true);
        } else {
            $this->_metaFlexer->log('fileName or package is not defined in ' . $templatePath);
        }
        return $f;
    }
}