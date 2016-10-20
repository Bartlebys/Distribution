<?php

require_once __DIR__ . '/FlexersConsts.php';
require_once __DIR__ . '/BJMParser.php';
require_once __DIR__ . '/PathsFlexer.php';
require_once __DIR__ . '/ProjectFlexer.php';
require_once __DIR__ . '/ActionFlexer.php';
require_once __DIR__ . '/EntityFlexer.php';
require_once __DIR__ . '/DescriptorsMerger.php';
require_once __DIR__ . '/Registry.php';

/***
 * Class MetaFlexer
 * MetaFlexer uses Flexions 2.0 templates and Supports two types of targets :
 * - "projects": multi passes full stack in memory generation with post generation phase
 * - "bunch": direct generation of bunches of Entities
 */
class MetaFlexer {

    /* @var PathsFlexer */
    public $pathsFlexer;

    /* @var EntityFlexer */
    public $entityFlexer;

    /* @var ProjectFlexer */
    public $projectFlexer;

    /* @var ActionFlexer */
    public $actionFlexer;

    /* @var string */
    public $appFolderPath;

    /* @var array an associative array modeling the raw templates graph */
    private $_templates = array();

    /* @var string */
    private $_generationFolderPath = '';

    /**
     * @return array
     */
    public function getTemplates() {
        return $this->_templates;
    }

    /* @var string the path of the metatemplate used to generate the app.{paths} */
    public $pathsMetaTemplatePath = null;

    /* @var array the entities default templates Descriptor */
    public $projectLoopTemplatesDescriptor = [];

    /* @var array the entities default templates Descriptor */
    public $entitiesLoopTemplatesDescriptor = [];

    /* @var array the entities default templates Descriptor */
    public $actionsLoopTemplatesDescriptor = [];

    /* @var string the post processor script relative path */
    public $postProcessorScript = null;


    /* @var string the project name */
    public $projectName = '';
    /* @var string the company */
    public $company = '';
    /* @var string the author */
    public $author = '';
    /* @var string the year */
    public $year = '';
    /* @var string the year */
    public $version = '0.0';

    /**
     * BartlebysAppMetaFlexer constructor.
     * @param string $appFolderPath
     */
    public function __construct($appFolderPath) {
        $this->appFolderPath = BJMParser::resolvePath($appFolderPath);
        $this->pathsFlexer = new  PathsFlexer($this);
        $this->projectFlexer = new ProjectFlexer($this);
        $this->entityFlexer = new EntityFlexer($this);
        $this->actionFlexer = new ActionFlexer($this);
    }


    /**
     * Run flexions from a configuration file that may contain multiple targets :
     * 1. "Projects targets" are full stacks including multiple layer (front, back-end , ...)
     * 2. "Bunches" are sets of Templates X Entity descriptors.
     * @param $path the configuration path.
     * @throws Exception
     */
    static public function buildWithConfiguration($path) {

        if (file_exists($path)) {
            fLog('Building with configuration file: '.$path.cr(),true);
            $json = file_get_contents($path);
            $configArray = json_decode($json, true);
            $configurationFolderPath=dirname($path);
            if (!is_array($configArray)) {
                throw  new Exception($path . ' must be a valid Json');
            }
            if (array_key_exists(FLEXIONS_CONFIGURATION_TARGETS_KEY, $configArray)) {
                $targets = $configArray[FLEXIONS_CONFIGURATION_TARGETS_KEY];
                if (!is_array($targets)) {
                    throw  new Exception(FLEXIONS_CONFIGURATION_TARGETS_KEY . ' must be an Array');
                }
                foreach ($targets as $targetName => $target) {
                    if (!is_array($target)) {
                        throw  new Exception($targetName . ' must be an Array');
                    }

                    // Reset the registry before to loop on a new Target
                    Registry::Instance()->reset();

                    // Reinject the top level variables.
                    if(array_key_exists(FLEXIONS_CONFIGURATION_VARIABLES,$configArray)){
                        if (is_array($configArray[FLEXIONS_CONFIGURATION_VARIABLES])){
                            $configVariablesDictionary=$configArray[FLEXIONS_CONFIGURATION_VARIABLES];
                            Registry::Instance()->defineVariables($configVariablesDictionary);
                        }
                    }

                    // Define the targets variables
                    if (array_key_exists(FLEXIONS_CONFIGURATION_VARIABLES, $target)) {
                        $variables = $target[FLEXIONS_CONFIGURATION_VARIABLES];
                        if(is_array($variables)){
                            Registry::Instance()->defineVariables($variables);
                        }else{
                            throw  new Exception('Configuration variables must be an Array');
                        }
                    }


                    ////////////////
                    // APPS TARGETS
                    ////////////////

                    // Apps targets are full stacks including multiple layer (front, back-end , ...)
                    // Bunches are sets of Templates + Entity descriptors.
                    if (array_key_exists(FLEXIONS_CONFIGURATION_PROJECT_KEY, $target)) {
                        $appFolderPath = $target[FLEXIONS_CONFIGURATION_PROJECT_KEY];
                        $appFlexer = new MetaFlexer($appFolderPath);

                        if (array_key_exists(FLEXIONS_CONFIGURATION_STAGE, $target)) {
                            $stage = $target[FLEXIONS_CONFIGURATION_STAGE];
                        } else {
                            $stage = DefaultStages::STAGE_DEVELOPMENT;
                        }
                        $appFlexer->generateApp($appFolderPath . '/out.flexions', true, $stage);
                    }


                    ////////////////////
                    // BUNCHES TARGETS
                    ////////////////////

                    // While Applications uses multi-passes and uses the Hypotypose to generate fully in memory
                    // Bunches are directly serialized to the destination.

                    if (array_key_exists(FLEXIONS_CONFIGURATION_BUNCH_KEY, $target)) {
                        $r=Registry::Instance();
                        if (is_string($target[FLEXIONS_CONFIGURATION_BUNCH_KEY])) {
                            $bunchFilePath = $target[FLEXIONS_CONFIGURATION_BUNCH_KEY];
                            $bunchFilePath = BJMParser::resolvePath($bunchFilePath,$configurationFolderPath);
                            MetaFlexer::_generateBunch($bunchFilePath,$configurationFolderPath);
                        } else {
                            throw  new Exception(FLEXIONS_CONFIGURATION_BUNCH_KEY . ' must be a string');
                        }
                    }
                }
            }

            // Dump the Flog
            $logFolderPath = dirname(FLEXIONS_ROOT_DIR) . '/out/logs/';
            if(! file_exists($logFolderPath)){
                mkdir ( $logFolderPath, 0755, true );
            }
            $logsFilePath = $logFolderPath . fDate () . '-logs.txt';
            file_put_contents ( $logsFilePath,Flog::Instance ()->getLogs () );

        } else {
            throw  new Exception('Unexisting path ' . $path);
        }
    }

    //////////////////////
    // BUNCH GENERATION
    //////////////////////


    /**
     * Generate a bunch of entities from a descriptor file path.
     * @param $bunchFilePath
     * @throws Exception
     */
    private static function _generateBunch($bunchFilePath,$configurationFolderPath='') {
        if (file_exists($bunchFilePath)) {
            $bunchFolderPath = dirname($bunchFilePath);
            $definitionsFolderPath = $bunchFolderPath . '/' . BJM_DEFINITIONS;

            // Load the bunch file.
            $bunchFileString = file_get_contents($bunchFilePath);
            $bunchArray = json_decode($bunchFileString, true);
            if (!is_array($bunchArray)) {
                throw  new Exception($bunchFilePath . '  is not a valid associative Array');
            }
            if (array_key_exists(BJM_BUNCH, $bunchArray)) {
                // We remove the bunch tag.
                $bunchArray = $bunchArray[BJM_BUNCH];
            }

            if(!array_key_exists(BJM_INFOS,$bunchArray)){
                throw  new Exception('"'.BJM_INFOS . '" tag is required in bunch definition');
            }
            $bunchInfosArray = $bunchArray[BJM_INFOS];
            if(!is_array($bunchInfosArray)){
                throw  new Exception('"'.BJM_INFOS . '" must be an associative array');
            }
            if (!array_key_exists(BJM_VERSION, $bunchInfosArray)) {
                throw  new Exception('"'.BJM_INFOS . '.'.BJM_VERSION.'" is mandatory in bunch definition');
            }

            // This mecanism replaces flexions the Shared.php file used in run session
            // To define global variables
            if (array_key_exists(BJM_VARIABLES, $bunchArray)) {
                $variables=$bunchArray[BJM_VARIABLES];
                Registry::Instance()->defineVariables($variables);
            }

            if (array_key_exists(BJM_BUNCH_EXPORT_PATH_CONSTANT, $bunchArray)) {
                $exportPathVariableName = $bunchArray[BJM_BUNCH_EXPORT_PATH_CONSTANT];
                $exportFolderPath = Registry::Instance()->valueForKey($exportPathVariableName);
                $exportFolderPath = BJMParser::resolvePath($exportFolderPath,$configurationFolderPath);
                if (!file_exists($exportFolderPath)) {
                    mkdir($exportFolderPath, 0755, true);
                }
                $flatExport = false;
                if(array_key_exists(BJM_BUNCH_FLAT_EXPORT,$bunchArray)){
                    $flatExport = $bunchArray[BJM_BUNCH_FLAT_EXPORT];
                }
                if (array_key_exists(BJM_LOOP_TEMPLATES, $bunchArray)) {
                    $templates = $bunchArray[BJM_LOOP_TEMPLATES];



                    if (is_array($templates)) {

                        ////////////////////
                        // DISCREET GENERATION
                        ///////////////////

                        $metaFlexer=new MetaFlexer($bunchFolderPath);
                        fLog(cr().'Generating bunch from '.$bunchFilePath.cr(),true);

                        $definitionPaths = directoryToArray($definitionsFolderPath);
                        foreach ($definitionPaths as $definitionPath) {
                            // Iterate on the templates
                            foreach ($templates as $template) {
                                if (is_array($template) && array_key_exists(BJM_PATH, $template)) {
                                    $entityFlexer = new EntityFlexer($metaFlexer);
                                    // Load the entity from its definition.
                                    $d = $entityFlexer->jsonToEntityRepresentation($definitionPath);
                                    // Reinitialize the Hypotypose and Flexed context
                                    $h = Hypotypose::NewInstance();
                                    if (array_key_exists(BJM_VERSION, $bunchInfosArray)) {
                                        $h->version = $bunchInfosArray[BJM_VERSION];
                                    }
                                    $f = new Flexed();
                                    if (array_key_exists(BJM_PROJECT_NAME, $bunchInfosArray)) {
                                        $f->projectName = $bunchInfosArray[BJM_PROJECT_NAME];
                                    }
                                    if (array_key_exists(BJM_COMPANY, $bunchInfosArray)) {
                                        $f->company = $bunchInfosArray[BJM_COMPANY];
                                    }
                                    if (array_key_exists(BJM_AUTHOR, $bunchInfosArray)) {
                                        $f->author = $bunchInfosArray[BJM_AUTHOR];
                                    }
                                    if (array_key_exists(BJM_YEAR, $bunchInfosArray)) {
                                        $f->year = $bunchInfosArray[BJM_YEAR];
                                    }

                                    // Resolve the template path
                                    $templatePath = BJMParser::resolvePath($template[BJM_PATH],$configurationFolderPath);
                                    ob_start();
                                    include $templatePath;
                                    $result = ob_get_clean();
                                    if ($flatExport) {
                                        $f->package = '';
                                    }
                                    $destination = $exportFolderPath . '/' . $f->package  . $f->fileName ;
                                    if (isset($result) && $result!==''){
                                        if(!file_exists(dirname($destination))){
                                            mkdir(dirname($destination), 0755, true);
                                        }
                                        @file_put_contents($destination, $result);
                                        fLog('Bunch Generation: '.$destination.cr(),true);
                                    }else{
                                        fLog('Bunch Generation: skipped '.$destination.cr(),true);
                                    }

                                } else {
                                    throw  new Exception('Path is unedefined. The json descriptor should contains "root.templates.' . BJM_PATH . '.*.path"');
                                }
                            }
                        }
                    } else {
                        throw  new Exception(BJM_LOOP_TEMPLATES . ' value is not an array of templates');
                    }
                }


            } else {
                throw  new Exception(BJM_BUNCH_EXPORT_PATH_CONSTANT . '  is undefined in ');
            }
        } else {
            throw  new Exception('Bunch configuration file not found. ' . $bunchFilePath);
        }
    }



    //////////////////////
    // APP GENERATION
    //////////////////////

    /**
     * Generates the app.
     * @param $generationFolderPath
     * @param bool $deletePreviousFiles
     * @param string $stage
     */
    public function generateApp($generationFolderPath, $deletePreviousFiles = true, $stage) {

        $this->_generationFolderPath = BJMParser::resolvePath($generationFolderPath);

        if ($deletePreviousFiles) {
            @rmdir($generationFolderPath);
        }

        /**
         * PRE-GENERATION
         *
         * During this phase we manipulate the descriptors files
         * 1.1 - Merge:  merges the app and definitions descriptors in a global derived descriptor.
         * 1.2 - Compute the endpoints - sort of "meta generation" (we use the project recursively generate its endpoints definitions)
         * 1.3 - Inject the endpoints
         * 1.4 - Configure the Hypotypose and the post processor
         * 1.5 - re-serialize the global descriptor.
         **/

        // 1.1 - Merge
        /* @var string $globalDescriptorPath */
        $globalDescriptorPath = $this->appFolderPath . '/descriptors/_derived/_global.json';
        $merger = new \Flexions\DescriptorsMerger($this->appFolderPath, $globalDescriptorPath);

        // 1.2 - Compute the endpoints
        $projectWithoutEndPoints = $this->projectFlexer->jsonToProjectRepresentation($globalDescriptorPath);
        $paths = $this->pathsFlexer->generateFromRepresentation($projectWithoutEndPoints, $this->pathsMetaTemplatePath);

        // 1.3- Inject the 'paths' into the global descriptor
        $globalDescriptorJson = file_get_contents($globalDescriptorPath);
        $globalDescriptorArray = json_decode($globalDescriptorJson, true);
        if (!is_array($globalDescriptorArray)) {
            throw new Exception("Global descriptor is not an Associative array");
        }

        // 1.4 Configure the Hypotypose
        // We reinitialize the singleton to prevent to reinject context from a previous target
        $h = Hypotypose::NewInstance();
        $h->classPrefix = '';
        $h->exportFolderPath = $generationFolderPath;
        $h->stage = $stage;
        $h->version=$this->version;

        // Set up the post-processor
        if (array_key_exists(BJM_POST_PROCESSOR, $globalDescriptorArray)) {
            $this->postProcessorScript = $globalDescriptorArray[BJM_POST_PROCESSOR];
        }

        // 1.5 - Save the global descriptor
        $globalDescriptorArray[BJM_PATHS] = $paths;
        // Re-encode to JSON
        $encodedGlobalDescriptor = json_encode($globalDescriptorArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($globalDescriptorPath, $encodedGlobalDescriptor);

        /**
         * GENERATION
         * During this phase we use Representation (generative models) and loop on templates
         **/

        // 2.1 Load the consolidated Project Representation
        /*@var ProjectRepresentation $project */
        $project = $this->projectFlexer->jsonToProjectRepresentation($globalDescriptorPath);

        // 2.2 Run the generative Loops
        $this->_entitiesLoop($project);
        $this->_actionsLoop($project);
        $this->_projectLoop($project);


        // 3. write the file to the standard output.
        hypotyposeToFiles();

        // 4. Run the Post Processor (hooks, deployment, restructuration , ...)

        if (isset($this->postProcessorScript) && strlen($this->postProcessorScript) > 5) {
            $postProcessorFilePath = $this->appFolderPath . '/' . $this->postProcessorScript;
            if (file_exists($postProcessorFilePath)) {
                include $postProcessorFilePath;
            }
        }

    }


    /**
     * Store the variables to the registry
     * - before using a template  to setup global scoped variables
     * - or to define an App wide global scoped variables
     * @param $descriptor
     */
    private function _storeTheVariablesToTheRegistryIfNecessary($descriptor) {
        if (array_key_exists(BJM_VARIABLES, $descriptor)) {
            if (is_array($descriptor[BJM_VARIABLES])){
               Registry::Instance()->defineVariables($descriptor[BJM_VARIABLES]);
            }
        }
    }

    /**
     * The entities loop is called  on each entity of the project
     * @param ProjectRepresentation $project
     */
    private function _entitiesLoop(ProjectRepresentation $project) {
        /*@var EntityRepresentation $entity */
        foreach ($project->entities as $entity) {
            foreach ($this->entitiesLoopTemplatesDescriptor as $descriptor) {
                $this->_storeTheVariablesToTheRegistryIfNecessary($descriptor);
                $templatePath = $descriptor[BJM_PATH];
                $flexed = $this->entityFlexer->generateFromRepresentation($entity, $templatePath, $this->_generationFolderPath);
                Hypotypose::Instance()->addFlexed($flexed);
            }
        }
    }

    /**
     * The actions loop is called on each action of the project
     * @param ProjectRepresentation $project
     */
    private function _actionsLoop(ProjectRepresentation $project) {
        /*@var ActionRepresentation $action */
        foreach ($project->actions as $action) {
            foreach ($this->actionsLoopTemplatesDescriptor as $descriptor) {
                $this->_storeTheVariablesToTheRegistryIfNecessary($descriptor);
                $templatePath = $descriptor[BJM_PATH];
                $flexed = $this->actionFlexer->generateFromRepresentation($action, $templatePath, $this->_generationFolderPath);
                Hypotypose::Instance()->addFlexed($flexed);
            }
        }
    }

    /**
     * The project loop is call once per Project
     * @param ProjectRepresentation $project
     */
    private function _projectLoop(ProjectRepresentation $project) {
        foreach ($this->projectLoopTemplatesDescriptor as $descriptor) {
            $this->_storeTheVariablesToTheRegistryIfNecessary($descriptor);
            $templatePath = $descriptor[BJM_PATH];
            $flexed = $this->projectFlexer->generateFromRepresentation($project, $templatePath, $this->_generationFolderPath);
            Hypotypose::Instance()->addFlexed($flexed);
        }
    }

    /**
     * Parses the root.templates section of descriptor.
     * @param array $templates
     */
    public function setTemplates($templates) {
        $this->_templates = $templates;
        if (is_array($this->_templates[BJM_LOOP_PATHS]) && array_key_exists(BJM_PATH, $this->_templates[BJM_LOOP_PATHS])) {
            $p = $this->_templates[BJM_LOOP_PATHS][BJM_PATH];
            $this->pathsMetaTemplatePath = $this->projectFlexer->resolve($p);
        } else {
            throw  new Exception('Paths meta template is unedefined. The json descriptor should contains "root.templates.' . BJM_LOOP_PATHS . '.' . BJM_PATH . '"');
        }
        $templatesMap = [
            [BJM_LOOP_PROJECT, 'projectLoopTemplatesDescriptor'],
            [BJM_LOOP_ENTITIES, 'entitiesLoopTemplatesDescriptor'],
            [BJM_LOOP_ACTIONS, 'actionsLoopTemplatesDescriptor']
        ];
        foreach ($templatesMap as $map) {
            $this->_parseTemplates($map[0], $map[1]);
        }
    }

    /**
     * Populates the mapped loops : $projectLoopTemplatesPath, $entitiesLoopTemplatesPath,$actionsLoopTemplatesPath
     * @param string $key
     * @param string $tplDescPropertyName
     * @throws Exception
     */
    private function _parseTemplates($key, $tplDescPropertyName) {
        if (is_array($this->_templates) && array_key_exists($key, $this->_templates)) {
            $loopTemplates = $this->_templates[$key];
            if (is_array($loopTemplates)) {
                foreach ($loopTemplates as $loopTemplate) {
                    if (is_array($loopTemplate) && array_key_exists(BJM_PATH, $loopTemplate)) {
                        // Resolve the template path
                        $loopTemplate[BJM_PATH] = $this->projectFlexer->resolve($loopTemplate[BJM_PATH]);
                        $this->{$tplDescPropertyName}[] = $loopTemplate;
                    } else {
                        throw  new Exception('Path is unedefined. The json descriptor should contains "root.templates.' . $key . '.*.path"');
                    }

                }
            } else {
                throw  new Exception($key . ' is not an array of templates');
            }
        } else {
            throw  new Exception($key . ' template is unedefined. The json descriptor should contains "root.templates.' . $key . '.path"');
        }
    }

    /**
     * @param $message
     */
    public function log($message) {
        fLog($message . cr());
    }
}