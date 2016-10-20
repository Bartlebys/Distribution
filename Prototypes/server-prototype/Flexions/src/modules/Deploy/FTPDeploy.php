<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 16:23
 */

// Dependency

require_once FLEXIONS_MODULES_DIR . '/Deploy/dependencies/FTP/FtpClient.php';
require_once FLEXIONS_MODULES_DIR . "/Deploy/Deploy.php";


class FTPDeploy extends Deploy implements IDeploy {

    /* @var $ftpClient \Melihucar\FtpClient\FtpClient */
    var $ftpClient;

    var $loggedIn = false;

    var $createdFolders = array();


    var $currentDirectory;

    function __construct(\Hypotypose $hypotypose) {
        $this->_hypotypose = $hypotypose;
        fLog(cr() . 'FTP deploy is running' . cr() . cr(), true);
    }

    function setUp($host, $port = 21) {
        $this->ftpClient = new \Melihucar\FtpClient\FtpClient();
        $this->ftpClient->connect($host, false, 21, 90);
    }

    function login($user, $password) {
        if (isset($this->ftpClient)) {
            $this->ftpClient->login($user, $password);
            $this->loggedIn = true;
        }
        return $this->loggedIn;
    }

    function rmPathImplementation($path) {
        if ($this->loggedIn == true) {
            // TODO implement the delete logic
        } else {
            throw new \Exception('Ftp client is not logged in login()');
        }
    }

    function copyFilesImplementation($filePath, $destination) {
        if ($this->loggedIn == true) {
            $folder = dirname($destination);
            $this->currentDirectory = $this->ftpClient->getDirectory();
            if ($folder != $this->currentDirectory) {
                // Create the directory
                $this->createDirectoriesForPath($destination);
                // GO TO THE DIRECTORY
                $cd = $this->ftpClient->changeDirectory(dirname($destination));
            }
            // UPLOAD
            $path_parts = pathinfo($destination);
            $destinationFileName = $path_parts['basename'];
            $upload = $this->ftpClient->put($destinationFileName, $filePath);
            fLog('FTP COPIED FROM : ' . $filePath . cr() . 'TO' . $destination . cr(), true);
        } else {
            throw new \Exception('Ftp client is not logged in login()');
        }

    }


    function createDirectoriesForPath($path) {
        $directory = dirname($path);
        if (in_array($directory, $this->createdFolders) == false) {
            // We need to create the folder.
            $baseFolder = $this->_absoluteBaseDestination;
            $this->cdToFolderPath($baseFolder);
            $relativeBaseDirectory = dirname(str_replace($this->_absoluteBaseDestination, '', $path));
            $relativeComponent = explode(DIRECTORY_SEPARATOR, $relativeBaseDirectory);
            foreach ($relativeComponent as $pathSegment) {
                $baseFolder .= $pathSegment . DIRECTORY_SEPARATOR;
                if (in_array($directory, $this->createdFolders) == false) {
                    $l = $this->ftpClient->listDirectory('');
                    if (is_array($l) && (!in_array($pathSegment, $l))) {
                        // Create the directory if necessary
                        $this->ftpClient->createDirectory($pathSegment);
                        $this->createdFolders[] = rtrim($baseFolder, '/');
                        fLog('FTP Created /' . $pathSegment . cr(), true);
                    }
                }
                $this->cdToFolderPath($baseFolder);
            }
        }
    }

    function cdToFolderPath($destination) {
        if (!isset($this->currentDirectory)) {
            $this->currentDirectory = $this->ftpClient->getDirectory();
        }
        $destinationDir = rtrim($destination, DIRECTORY_SEPARATOR);
        if ($this->currentDirectory != $destinationDir) {
            $relativePath = str_replace($this->currentDirectory, '', $destination);
            $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $symRelativePath = str_replace($destination, '', $this->currentDirectory);
            if ($symRelativePath == $this->currentDirectory) {
                // We are down in the hierarchy
                $delta = explode('/', $relativePath);
                foreach ($delta as $deltaSegment) {
                    if ($deltaSegment != '') {
                        $this->ftpClient->changeDirectory($deltaSegment);
                        $this->currentDirectory .= DIRECTORY_SEPARATOR . $deltaSegment;
                    }
                }
            } else {
                // We are up in the hierarchy
                while ($this->currentDirectory != $destinationDir) {
                    $result = $this->ftpClient->parentDirectory();
                    $this->currentDirectory = dirname($this->currentDirectory);
                }
            }
        }
        fLog('FTP Changed directory to ' . $this->currentDirectory . cr(), true);

    }


}