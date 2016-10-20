<?php
/*

Copyright 2O16 Benoit Pereira da Silva https://pereira-da-silva.com

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/
namespace Bartleby;

use \ZipArchive;
use \Exception;

// @todo
// # crypto support
// # VARIABLES INJECTION SUPPORT from a JSON mapping file.

class Flocker {

    // METADATA Starts and Ends Tags
    const BUNDLER_METADATA_STARTS = "#BMS#->";
    // BETWEEN = The bundler metadate are JSON ENCODED
    const BUNDLER_METADATA_ENDS = "<-#BME#\n";

    // METADATA KEYS
    const BUNDLER_FILE_NAME_KEY = 'filename';
    const BUNDLER_SIZE_KEY = 'size';
    const BUNDLER_CHECKSUM_KEY = 'checksum';
    const BUNDLER_RELATIVE_PATH_KEY = 'relativePath';

    static private $VERBOSE = true;
    static private $USE_ZIP = true;// Creates a zipped version of the Bundle.package file
    static private $PRESERVE_UNZIPPED_BUNDLE = true; // Preserves the Bundle.package file
    static private $ENCODE_METADATA_TAGS = false;

    /**
     * @var bool
     */
    private $_userCommandLineMode = true;


    /**
     * @var array
     */
    private $_rawArguments = array();

    /**
     * @var array
     */
    private $_arguments = array();

    /**
     * Flocker constructor.
     */
    public function __construct() {
        // The argument can also be defined from a boot php script
        if (!isset($arguments)) {
            // Server & commandline versatile support
            if ($_SERVER ['argc'] == 0 || !defined('STDIN')) {
                // Server mode
                $this->_arguments = $_GET;
                $this->_userCommandLineMode = false;
            } else {
                // Command line mode
                $rawArgs = $_SERVER ['argv'];
                array_shift($rawArgs); // shifts the commandline script file

                $nbOfArgs = count($rawArgs);

                if ($nbOfArgs > 0 && $rawArgs[0] == "args") {
                    array_shift($rawArgs);
                    $nbOfArgs = count($rawArgs);
                }

                if ($nbOfArgs % 2 == 0) {
                    if ($rawArgs > 0) {
                        for ($i = 0; $i < $nbOfArgs; $i = $i + 2) {
                            $flag = $rawArgs[$i];
                            $flag = str_replace('--', '', $flag);
                            $flag = str_replace('-', '', $flag);
                            $value = $rawArgs[$i + 1];
                            $this->_arguments[$flag] = $value;
                        }
                    }
                } else {
                    throw new \Exception('Arguments flag / value parity issue');
                }
                $this->_userCommandLineMode = true;

            }

        }
    }

    /**
     * This method can accept arguments from a commandline or by GET
     * "source
     * @throws \Exception
     */
    function build() {
        // Arguments?
        if (array_key_exists('source', $this->_arguments)) {
            $mapPath = $this->_arguments['source'];

            $mapString = file_get_contents($mapPath);
            $map = json_decode($mapString);

            $parentFolder = dirname(realpath($mapPath)) . DIRECTORY_SEPARATOR;

            $modules = $map->modules;
            $distributionFolderPath = $map->distributionFolderPath;
            $distributionPath = $map->distributionPath . isset($map->version) ? $map->version : '';

            foreach ($modules as $module) {
                $moduleSourcePath = $parentFolder . $module->path;
                /* @var array */
                $excludeFiles = $module->exclude;
                $destination = $parentFolder . $distributionFolderPath . basename($moduleSourcePath);
                $this->_copyFile($moduleSourcePath, $destination);
            }

        } else {
            throw new \Exception('You must provide a map');
        }
    }


    /**
     * This method can accept arguments from a commandline or by GET
     * "source"
     * It returns a string encoded to be embedded in Swift
     * @throws \Exception
     */
    function encodeForSwift() {
        // Arguments?
        if (array_key_exists('source', $this->_arguments)) {
            $path = $this->_arguments['source'];
            $content = file_get_contents($path);
            $content = str_replace('<?php', "\n", $content);
            $content = str_replace('?>', "\n", $content);
            $content = str_replace('\\', '\\\\', $content);
            $content = str_replace("\n", '\\n', $content);
            $content = str_replace("\t", '\\t', $content);
            $spaces = '  ';
            for ($i = 0; $i < 8; $i++) {
                $content = str_replace($spaces, ' ', $content);
                $spaces .= ' ';
            }
            $content = str_replace('"', '\"', $content);
            echo "\n\n" . $content . "\n\n";
        } else {
            throw new \Exception('You must provide a source');
        }
    }


    /**
     * This method can accept arguments from a commandline or by GET
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Distribution/Bundle-sources/'
     *  Default destination is set to dirname(__DIR__).'/Distribution/Bundle.package'
     *
     * @throws \Exception
     */
    function pack($directoryPath = '', $outputPath = '',$useZIP=true) {

        Flocker::$USE_ZIP=$useZIP;

        if ($directoryPath == '') {
            // Default
            $directoryPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Distribution' . DIRECTORY_SEPARATOR . 'Bundle-sources' . DIRECTORY_SEPARATOR;
        }
        if ($directoryPath == '') {
            // Default
            $outputPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Distribution' . DIRECTORY_SEPARATOR . 'Bundle.package';
        }
        // Arguments?
        if (array_key_exists('source', $this->_arguments)) {
            $directoryPath = $this->_arguments['source'];
        }
        if (array_key_exists('destination', $this->_arguments)) {
            $outputPath = $this->_arguments['destination'];
        }
        $this->_packFilesFromDirectory($directoryPath, $outputPath);
    }

    /**
     *  This method can accept arguments from a commandline, by GET parameters or function arguments
     * "source" , "destination"
     *  Default source is set to dirname(__DIR__).'/Bundle.package.zip'
     *  Default destination is set to dirname(__DIR__).'/ExpandedBundle/'
     *
     * @param string|null $bundledFilePath
     * @param string|null $destinationFolderPath
     * @param bool $useZIP
     * @throws Exception
     */
    function unPack($bundledFilePath = NULL, $destinationFolderPath = NULL,$useZIP=true) {

        Flocker::$USE_ZIP=$useZIP;

        if (!isset($bundledFilePath)) {
            //Default
            $bundledFilePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bundle.package';
        }
        if (Flocker::$USE_ZIP) {
            $bundledFilePath .= '.zip';
        }
        if (!isset($destinationFolderPath)) {
            //Default
            $destinationFolderPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Expanded' . DIRECTORY_SEPARATOR;
        }

        // Arguments?
        if (array_key_exists('source', $this->_arguments)) {
            $bundledFilePath = $this->_arguments['source'];
        }
        if (array_key_exists('destination', $this->_arguments)) {
            $destinationFolderPath = $this->_arguments['destination'];
        }
        $this->_unPackFiles($bundledFilePath, $destinationFolderPath);
    }

    /***
     * The private implementation
     *
     * @param $directoryPath
     * @param $outputPath
     * @throws \Exception
     */
    private function _packFilesFromDirectory($directoryPath, $outputPath) {
        if (!file_exists($directoryPath)) {
            throw new \Exception("Path do no exists" . $directoryPath);
        }
        $fHandle = fopen($outputPath, "wb");
        if ($fHandle === false) {
            throw new \Exception("Open destination handle Error " . $outputPath);
        }
        $iterators = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath));
        foreach ($iterators as $fileDetails) {
            $exclusionList = ['.DS_Store'];
            /* @var $fileDetails \SplFileInfo */
            $fileName = $fileDetails->getFilename();


            $filePath = $fileDetails->getPathname();
            $filesize = filesize($filePath);
            $relativePath = str_replace($directoryPath, "", $filePath);
            $extension = pathinfo($fileDetails->getPath());

            if (in_array($fileName, $exclusionList)) {
                if (Flocker::$VERBOSE === true) {
                    echo "Skipping " . $filePath . "\n";
                }
                continue;
            }

            if (Flocker::$VERBOSE === true) {
                echo "Packaging " . $fileName . "\n";
                echo $filePath . "\n";
            }

            $isDir = $fileDetails->isDir();

            if ($fileName != ".." &&
                $fileName != "."
            ) {

                $readInFile = NULL;
                if (!$isDir) {
                    $rHandle = fopen($filePath, "rb");
                    if ($rHandle == false) {
                        throw new \Exception("Error open file to read from " . $filePath);
                    }
                    $readInFile = fread($rHandle, $filesize);
                    if ($readInFile == false) {
                        throw new \Exception("Error on reading file to read from " . $filePath);
                    }
                    fclose($rHandle);
                }
                $checksum = 0;
                if (!$isDir) {
                    $checksum = crc32($readInFile);
                }

                //  Write the METADATA Array
                $metadataString = $this->_protectTag(Flocker::BUNDLER_METADATA_STARTS);
                $metadataArray = array(
                    Flocker::BUNDLER_FILE_NAME_KEY => $fileName,
                    Flocker::BUNDLER_CHECKSUM_KEY => $checksum,
                    Flocker::BUNDLER_RELATIVE_PATH_KEY => $relativePath,
                    Flocker::BUNDLER_SIZE_KEY => $filesize
                );
                $metadataJson = json_encode($metadataArray);
                $metadataString .= $metadataJson;
                $metadataString .= $this->_protectTag(Flocker::BUNDLER_METADATA_ENDS);
                if (fwrite($fHandle, $metadataString) === false) {
                    throw new \Exception("Error writing Flocker metadata for ");
                }

                // Write the The file Content
                if (!$isDir) {
                    if (fwrite($fHandle, $readInFile) === false) {
                        throw new \Exception("Error writing binary data");
                    }
                }
            }
        }
        fclose($fHandle);

        if (Flocker::$USE_ZIP) {
            if (file_exists($outputPath . '.zip')) {
                unlink($outputPath . '.zip');
            }
            $zip = new ZipArchive();
            if ($zip->open($outputPath . '.zip', ZipArchive::CREATE) !== TRUE) {
                throw new \Exception("ZipArchive was not able to open <$outputPath.zip>\n");
            };
            $zip->addFile($outputPath, basename($outputPath));
            $zip->close();
            if (!Flocker::$PRESERVE_UNZIPPED_BUNDLE) {
                unlink($outputPath);
            }
        }
    }

    private function _unPackFiles($fileToReadFrom, $fileDirectoryToWrite) {


        if (Flocker::$USE_ZIP) {
            $unzippedFilePath = str_replace(".zip", "", $fileToReadFrom);
            $zip = new ZipArchive();
            if ($zip->open($fileToReadFrom) !== TRUE) {
                throw new \Exception("ZipArchive was not able to open <$fileToReadFrom>\n");
            }
            if (file_exists($unzippedFilePath)) {
                unlink($unzippedFilePath);
            }

            if ($zip->extractTo($unzippedFilePath) === true) {
                throw new \Exception("ZipArchive was not able to extract <$fileToReadFrom>\n");
            }
            // use the unzipped file
            $fileToReadFrom = $unzippedFilePath;
        }

        if (file_exists($fileDirectoryToWrite) === false) {
            mkdir($fileDirectoryToWrite);
        }

        $rHandle = fopen($fileToReadFrom, "rb");
        if ($rHandle == false) throw new \Exception("Error opening file to read");
        while (!feof($rHandle)) {

            $nextLine = fgets($rHandle); // read one line

            if ($nextLine !== false) {
                $jsonString = str_replace($this->_protectTag(Flocker::BUNDLER_METADATA_STARTS), '', $nextLine);
                $jsonString = str_replace($this->_protectTag(Flocker::BUNDLER_METADATA_ENDS), '', $jsonString);
                $metadata = json_decode($jsonString, true);
                if (is_array($metadata)) {
                    if (array_key_exists(Flocker::BUNDLER_FILE_NAME_KEY, $metadata) &&
                        array_key_exists(Flocker::BUNDLER_RELATIVE_PATH_KEY, $metadata) &&
                        array_key_exists(Flocker::BUNDLER_SIZE_KEY, $metadata) &&
                        array_key_exists(Flocker::BUNDLER_CHECKSUM_KEY, $metadata)
                    ) {
                        $filename = $metadata[Flocker::BUNDLER_FILE_NAME_KEY];
                        $relativePath = $metadata[Flocker::BUNDLER_RELATIVE_PATH_KEY];
                        $filesize = $metadata[Flocker::BUNDLER_SIZE_KEY];
                        $checksum = $metadata[Flocker::BUNDLER_CHECKSUM_KEY];

                        $absolutePath = $fileDirectoryToWrite . $relativePath;

                        if ($filesize === 0) {
                            // It is a folder
                            // Let's try to recreate the folder
                            mkdir($absolutePath);
                            continue;
                        }

                        $bytes = fread($rHandle, $filesize);
                        if ($bytes === false) {
                            throw new \Exception("Error reading bytes");
                        }

                        $parentFolder = dirname($absolutePath);
                        if (file_exists($parentFolder) == false) {
                            mkdir(dirname($absolutePath), 0777, true);
                        }

                        $fHandle = fopen($absolutePath, "wb");

                        if (Flocker::$VERBOSE === true) {
                            echo "Un-packing " . $relativePath . "\n";
                        }

                        if ($fHandle === false) {
                            throw new \Exception("Error opening writing to file");
                        }

                        // write the bytes
                        if (fwrite($fHandle, $bytes) === false) {
                            throw new \Exception("Error writing to file");
                        }
                        fclose($fHandle);

                    } else {
                        throw new \Exception("Unconsistent metadata");
                    }
                } else {
                    throw new \Exception("Metadata extraction");
                }
                $absolutePath = NULL;
            }

        }
        fclose($rHandle);
        if (Flocker::$USE_ZIP) {
            unlink($unzippedFilePath);
        }
    }

    // TAGS 


    private function _protectTag($tag) {
        if (Flocker::$ENCODE_METADATA_TAGS) {
            return md5($tag);
        } else {
            return $tag;
        }
    }

    private function _encodeTags($string) {
        $string = str_replace(Flocker::BUNDLER_METADATA_STARTS, $this->_protectTag(Flocker::BUNDLER_METADATA_STARTS), $string);
        $string = str_replace(Flocker::BUNDLER_METADATA_ENDS, $this->_protectTag(Flocker::BUNDLER_METADATA_ENDS), $string);
        return $string;
    }


    private function _decodeTags($string) {
        $string = str_replace($this->_protectTag(Flocker::BUNDLER_METADATA_STARTS), Flocker::BUNDLER_METADATA_STARTS, $string);
        $string = str_replace($this->_protectTag(Flocker::BUNDLER_METADATA_ENDS), Flocker::BUNDLER_METADATA_ENDS, $string);
        return $string;
    }


    ////////////////////
    // FILE UTILITIES
    ////////////////////


    private function _copyFile($filePath, $destination) {
        @mkdir(dirname($destination));
        try {
            copy($filePath, $destination);
        } catch (\Exception $e) {
            echo('error on copy ' . $filePath . '->' . $destination);
        }
    }

    //@todo
    private function _copyPath($folderPath, $destination, $relativePath = NULL, $exclusionList = NULL) {

    }

    private function _removePath($path) {
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file)
                if ($file != "." && $file != "..") $this->_removePath($path . DIRECTORY_SEPARATOR . $file);
            rmdir($path);
        } else if (file_exists($path)) unlink($path);
    }
}

?>