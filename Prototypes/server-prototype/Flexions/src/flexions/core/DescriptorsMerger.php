<?php

namespace Flexions;

require_once __DIR__ . '/FlexersConsts.php';
require_once __DIR__ . '/BJMParser.php';

/***
 * Class DescriptorsMerger
 * @package Flexions
 */
class DescriptorsMerger {

    /**
     * DescriptorsMerger constructor
     *
     * @param $originFolderPath string  the original descriptor folder including the `project.json` and `definitions/*.json`
     * @param $consolidatedDescriptorPath string
     */
    public function __construct($originFolderPath, $consolidatedDescriptorPath) {

        $originFolderPath=\BJMParser::resolvePath($originFolderPath);
        $consolidatedDescriptorPath=\BJMParser::resolvePath($consolidatedDescriptorPath);

        if(!file_exists(dirname($consolidatedDescriptorPath))){
            mkdir(dirname($consolidatedDescriptorPath),0755,true);
        }

        $definitionsFolder = $originFolderPath . '/descriptors/definitions';
        $consolidatedDescriptor = array();
        // Erase the current definitions
        $consolidatedDescriptor[BJM_DEFINITIONS] = array();
        $dirList = scandir($definitionsFolder);
        foreach ($dirList as $key => $filename) {
            $dotPos = strpos($filename, '.');
            if (($dotPos === false) or ($dotPos != 0)) {
                fLog('Merging ->' . $filename . cr(),true);
                $defString = file_get_contents($definitionsFolder . '/' . $filename);
                $defArray = json_decode($defString, true);
                if (is_array($defArray)) {
                    if (array_key_exists(BJM_NAME, $defArray) && array_key_exists(BJM_DEFINITION, $defArray)) {
                        $consolidatedDescriptor[BJM_DEFINITIONS][$defArray[BJM_NAME]] = $defArray[BJM_DEFINITION];
                    } else {
                        fLog('$filename is not valid descriptor' .cr(),true);
                    }
                } else {
                    fLog('$filename is not valid' . cr(),true);
                }
            }
        }
        $appDescriptorFile = $originFolderPath . '/descriptors/project.json';
        if (file_exists($appDescriptorFile)) {
            $desc = file_get_contents($appDescriptorFile);
            $jsonAppDesc = json_decode($desc, true);

            // We can merge Only apps not bunches.

            if (array_key_exists(BJM_PROJECT,$jsonAppDesc)){
                $descriptor=$jsonAppDesc[BJM_PROJECT];
                if (is_array($descriptor)) {
                    $appKeys = [
                        BJM_INFOS,
                        BJM_HOST,
                        BJM_BASE_PATH,
                        BJM_TAGS,
                        BJM_SCHEMES,
                        BJM_EXTERNAL_DOCS,
                        BJM_LOOP_TEMPLATES,
                        BJM_VARIABLES,
                        BJM_POST_PROCESSOR
                    ];
                    foreach ($appKeys as $key) {
                        if (array_key_exists($key, $descriptor)) {
                            $consolidatedDescriptor[$key] = $descriptor[$key];
                        }
                    }
                }
            }else{
                throw new Exception(BJM_PROJECT.' tag is missing');
            }

        }
        $jsonString = json_encode($consolidatedDescriptor, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        file_put_contents($consolidatedDescriptorPath, $jsonString);
    }


}
