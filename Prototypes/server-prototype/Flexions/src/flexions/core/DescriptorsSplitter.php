<?php

namespace Flexions;

require_once __DIR__ . '/FlexersConsts.php';

/***
 * Class DSSplitter
 * Quick Definitions extractor.
 * It splits the monolitic legacy datasources into editable components
 * It is the opposite of the DescriptorsMerger
 *
 * e.g:
 * new DescriptorsSplitter('/Users/bpds/Documents/Entrepot/Git/Clients/LyLo.TV/YouDubAPI/Bartleby/Commons.flexions/App/out.flexions/bartleby.json');
 * new DescriptorsSplitter('/Users/bpds/Documents/Entrepot/Git/Clients/LyLo.TV/YouDubAPI/YouDubApi.flexions/App/out.flexions/youdub.json');
 *
 * @package Flexions
 **/
class DescriptorsSplitter {

    /**
     * DSSplitter constructor.
     * @param $originalDescriptorFilepath string the consolidated json descriptor to be splitted
     * @param string $destinationFolder string the destination folder of the splitted descriptors
     */
    public function __construct($originalDescriptorFilepath, $destinationFolder = '') {
        if ($destinationFolder == '') {
            // Default value is conventionnal.
            $destinationFolder = dirname(dirname($originalDescriptorFilepath));
        }
        if (file_exists($originalDescriptorFilepath)) {
            fLog('Loading ' . $originalDescriptorFilepath . cr(),true);
            $jsonString = file_get_contents($this->$originalDescriptorFilepath);
            $consolidatedDataSource = json_decode($jsonString, true);
            if (is_array($consolidatedDataSource)) {
                fLog('Deleting ' . $destinationFolder . cr(),true);
                @unlink($destinationFolder);
                fLog('Recreating ' . $destinationFolder . cr(),true);
                @mkdir($destinationFolder, 0755, true);
                if (array_key_exists(BJM_DEFINITIONS, $consolidatedDataSource)) {
                    $definitions = $consolidatedDataSource[BJM_DEFINITIONS];
                    foreach ($definitions as $name => $def) {
                        $definition = array(BJM_NAME => $name, BJM_DEFINITION => $def);
                        $j = json_encode($definition, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                        fLog('Splitting -> ' . $name . '.json' . cr(),true);
                        file_put_contents($destinationFolder . '/' . $name . '.json', $j);
                    }
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
                    $appArray = [BJM_PROJECT=>[]];
                    foreach ($appKeys as $key) {
                        if (array_key_exists($key, $consolidatedDataSource)) {
                            $appArray[BJM_PROJECT][$key] = $consolidatedDataSource[$key];
                        }
                    }
                    $appDescriptorPath = $destinationFolder . '/project.json';
                    $appDJson = json_encode($appArray, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    file_put_contents($appDescriptorPath, $appDJson);
                } else {
                    fLog($originalDescriptorFilepath . ' is not a valid file.' . cr(),true);
                }

            } else {
                fLog($originalDescriptorFilepath . ' does not exists' . cr(),true);
            }

        } else {

        }

    }
}
