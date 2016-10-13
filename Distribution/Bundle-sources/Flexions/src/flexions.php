<?php
/**
 * This script uses flexions 3.0 AppMetaFlexer to build a set of targets
 * defined in configuration file.
 */
require_once __DIR__ . '/Flexions/core/MetaFlexer.php';
require_once __DIR__ . '/Flexions/core/DiscreetFlexer.php';

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');
define('CONFIGURATION_ARGUMENT_KEY', 'configuration');

if (!isset($arguments)) {
    // Server & commandline versatile support
    if ($_SERVER ['argc'] == 0 || !defined('STDIN')) {
        // Server mode
        $arguments = $_GET;
        define("COMMANDLINE_MODE", false);
    } else {
        // Command line mode
        $rawArgs = $_SERVER ['argv'];
        array_shift($rawArgs); // shifts the commandline script file flexions.php
        $arguments = array();
        parse_str(implode('&', $rawArgs), $arguments);
        define("COMMANDLINE_MODE", true);
    }
} else {
    // The arguments can also be defined from a boot php script
}

if (is_array($arguments)) {
    if (array_key_exists(CONFIGURATION_ARGUMENT_KEY, $arguments)) {

        ////////////////
        // FULL BUILD
        ///////////////

        MetaFlexer::buildWithConfiguration($arguments[CONFIGURATION_ARGUMENT_KEY]);
    } else {

        //////////////////
        // DISCREET BUILD
        //////////////////

        $templatePath = NULL;
        $entityDescriptionPath = NULL;
        $destinationPath = NULL;
        $version = NULL;
        $projectName = NULL;
        $company = NULL;
        $author = NULL;
        $year = NULL;
        $deleteTempFiles = NULL;
        $variables = array();

        // We need to insure we have all the mandatory arguments.

        if (array_key_exists('templatePath', $arguments)) {
            $templatePath = $arguments['templatePath'];
        } else {
            fLog('We need "templatePath" argument' . cr(), true);
            return;
        }
        if (array_key_exists('entityDescriptionPath', $arguments)) {
            $templatePath = $arguments['entityDescriptionPath'];
        } else {
            fLog('We need "entityDescriptionPath" argument' . cr(), true);
            return;
        }

        if (array_key_exists('$destinationPath', $arguments)) {
            $templatePath = $arguments['$destinationPath'];
        } else {
            fLog('We need "$destinationPath" argument' . cr(), true);
            return;
        }

        // Then set the 'optionals'
        if (array_key_exists('version', $arguments)) {
            $version = $arguments['version'];
        }
        if (array_key_exists('projectName', $arguments)) {
            $projectName = $arguments['projectName'];
        }
        if (array_key_exists('company', $arguments)) {
            $company = $arguments['company'];
        }
        if (array_key_exists('author', $arguments)) {
            $author = $arguments['author'];
        }
        if (array_key_exists('year', $arguments)) {
            $year = $arguments['year'];
        }
        if (array_key_exists('deleteTempFiles', $arguments)) {
            $deleteTempFiles = $arguments['deleteTempFiles'];
        }
        if (array_key_exists('variables', $arguments)) {
            $variables = $arguments['variables'];
        }

        // And finally Invoke the discreet flexer
        DiscreetFlexer::buildDiscreet($templatePath, $entityDescriptionPath, $destinationPath, $version, $projectName, $company, $author, $year, $deleteTempFiles, $variables);
    }
} else {
    echo(CONFIGURATION_ARGUMENT_KEY . ' argument is unedefined');
}