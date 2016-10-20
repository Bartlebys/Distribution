<?php

require_once __DIR__ . '/MetaFlexer.php';

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 11/10/2016
 * Time: 19:28
 */
class DiscreetFlexer {

    //////////////////////
    // DISCREET GENERATION
    //////////////////////

    /**
     * This method  creates a temporary a bunch with one template and description + its configuration
     * And call buildWithConfiguration($conf_path)
     * Then delete the temporary bunch
     *
     * @param $templatePath
     * @param $entityDescriptionPath
     * @param $destinationPath
     * @param string $version
     * @param string $projectName
     * @param string $company
     * @param string $author
     * @param string $year
     * @param bool $deleteTempFiles
     * @param array $variables (if your templates require some globals you can set them in this dictionary)
     */
    static public function buildDiscreet($templatePath, $entityDescriptionPath, $destinationPath, $version = '1.0', $projectName = 'NO_PROJECT_NAME', $company = 'NO_COMPANY', $author = 'NO_AUTHOR', $year = '2000', $deleteTempFiles = false, $variables = array()) {

        $uniqueID = uniqid();
        $tempFolder = dirname($destinationPath) . '/_derived';
        @mkdir($tempFolder,0755,true);

        // Create the bunch.json file.
        $bunchFilePath = $tempFolder . '/bunch.json';
        $exportPathVariableName = DiscreetFlexer::createBunchFile($uniqueID, $bunchFilePath, $templatePath, $version, $projectName, $company, $author, $year, $variables);

        // Create the configuration file
        $minimalConfigurationFile = array(
            FLEXIONS_CONFIGURATION_TARGETS_KEY => array(
                "temp-name" => array(
                    "bunch" => $bunchFilePath,
                    FLEXIONS_CONFIGURATION_VARIABLES => array($exportPathVariableName => $destinationPath) // inject the export path in the configuration
                )
            )
        );

        $confJson = json_encode($minimalConfigurationFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $configurationPath = $tempFolder . '/configuration.json';
        file_put_contents($configurationPath, $confJson);

        $entityDescriptionDir = dirname($entityDescriptionPath);
        $dirName = basename($entityDescriptionDir);
        $definitionsFolder = $tempFolder . '/definitions';
        @mkdir($definitionsFolder,0755,true);

        if ($dirName == 'definitions') {
            $files = directoryToArray($entityDescriptionDir);
            foreach ($files as $file) {
                $destination = $definitionsFolder . '/' . basename($file);
                copy($file, $destination);
            }
        } else {
            copy($entityDescriptionPath, $definitionsFolder . '/' . basename($entityDescriptionPath));
        }
        // Invoke the Standard build.
        MetaFlexer::buildWithConfiguration($configurationPath);

        // Delete the temp files if necessary
        if ($deleteTempFiles) {
            if (deleteDirectory($tempFolder)){
                fLog('Failed to delete '.$tempFolder.cr());
            }

        }
    }


    /***
     * Creates a bunch file
     *
     * @param $uniqueID
     * @param $bunchFilePath
     * @param $templatePath
     * @param string $version
     * @param string $projectName
     * @param string $company
     * @param string $author
     * @param string $year
     * @param array $variables
     * @return string, the export path variable name
     *
     */
    static public function createBunchFile($uniqueID, $bunchFilePath, $templatePath, $version = '1.0', $projectName = 'NO_PROJECT_NAME', $company = 'NO_COMPANY', $author = 'NO_AUTHOR', $year = '2000', $variables = array()) {

        $exportPathVariableName = 'exportPath_' . strtoupper($uniqueID);
        // Let's create the bunch
        $bunchDictionary = array(
            "bunch" => array(
                "infos"=> array(
                    "version" => $version,
                    "projectName" => $projectName,
                    "company" => $company,
                    "author" => $author,
                    "year" => $year
                ),
                "templates" => array(
                    array(
                        "path" => $templatePath,
                        "description" => ""
                    )
                ),
                "definitions" => "/",
                "exportPathVariableName" => $exportPathVariableName,
                "flatExport" => true,
                "variables" => $variables
            ));
        $bunchJson = json_encode($bunchDictionary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($bunchFilePath, $bunchJson);

        return $exportPathVariableName;
    }
}

