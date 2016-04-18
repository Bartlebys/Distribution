<?php

interface IFlexionsLanguageMapping{

    /**
     * @param  $flexionsType
     * @return String the native type
     */
    static function nativeTypeFor($flexionsType);

}