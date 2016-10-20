<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 16:23
 */


require_once "Deploy.php";

class LocalDeploy extends Deploy implements IDeploy {

    function __construct(\Hypotypose $hypotypose) {
        $this->_hypotypose = $hypotypose;
        fLog(cr() . 'LOCAL deploy is running' . cr() . cr(), true);
    }

    function rmPathImplementation($path) {
        $this->_delete($path);
    }

    /**
     * Deletes a file or recursively a folder
     * Returns true if the file or the folder does not exists.
     * @see IOManagerPersistency::delete()
     * @param $filename
     * @return bool
     */
    private function _delete($filename) {
        if (!file_exists($filename)) {
            return true;
        }
        if (is_dir($filename)) {
            // we delete folders with a recursive deletion method
            return $this->_rmdir($filename, true);
        } else {
            return unlink($filename);
        }
    }

    /**
     * @param $dir
     * @param $result
     * @return bool
     */
    private function _rmdir($dir, $result) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir")
                        $result = $result && $this->_rmdir($dir . DIRECTORY_SEPARATOR . $object, $result);
                    else
                        $result = $result && unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            $result = $result && rmdir($dir);
        }
        return $result;
    }


    function copyFilesImplementation($filePath, $destination) {
        $directory=dirname($destination);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        fLog('COPYING FROM : ' . $filePath . cr() . 'TO' . $destination . cr(), true);
        copy($filePath, $destination);
    }
}