<?php

require_once FLEXIONS_ROOT_DIR . '/flexions/core/Hypotypose.php';

class GenerativeHelper {

    static function defaultHeader(Flexed $f, $d) {
        return '// DEFAULT HEADER';
    }

    static function variablesFromPath($path) {
        preg_match_all('/{(.*?)}/', $path, $matches);
        return $matches[1];
    }

}
