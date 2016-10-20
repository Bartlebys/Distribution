<?php


require_once FLEXIONS_ROOT_DIR . '/flexions/core/Enum.php';

/**
 * Class FlexionsTypes
 * Enumerates the "primitive" type of Flexions as const
 */
class FlexionsTypes extends Enum {
    // Basic types
    const STRING = 'string';
    const INTEGER = 'integer';
    const BOOLEAN = 'boolean';
    const FLOAT = 'float';
    const DOUBLE = 'double';
    //
    const BYTE = 'byte';
    const DATETIME = 'dateTime';
    const URL = 'url';
    const DATA = 'data';

    const FILE = 'file';

    // Generic structure
    const DICTIONARY = 'dictionary';
    //
    const OBJECT = 'object'; // Used to reference an $instanceOf
    const COLLECTION = 'collection';// Used to reference a collection of $instanceOf that can be any FlexionsType
    const ENUM = 'enum';// Used to reference a enumeration of $instanceOf that can be any FlexionsType
    //
    const NOT_SUPPORTED = 'Not_Supported';

    const VOID = 'void';

    /**
     * @return array
     */
    static function possibleValues() {
        return array(
            FlexionsTypes::STRING,
            FlexionsTypes::INTEGER,
            FlexionsTypes::BOOLEAN,
            FlexionsTypes::FLOAT,
            FlexionsTypes::DOUBLE,
            FlexionsTypes::BYTE,
            FlexionsTypes::DATETIME,
            FlexionsTypes::URL,
            FlexionsTypes::FILE,
            FlexionsTypes::DICTIONARY,
            FlexionsTypes::OBJECT,
            FlexionsTypes::COLLECTION,
            FlexionsTypes::ENUM,
            FlexionsTypes::NOT_SUPPORTED,
            FlexionsTypes::VOID,
            FlexionsTypes::DATA
        );
    }
}