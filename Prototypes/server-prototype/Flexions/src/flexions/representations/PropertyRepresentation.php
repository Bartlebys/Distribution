<?php

/*
 Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.chaosmos.fr

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/Licenses/>
*/

class Method{
    const IS_INSTANCE = 'instance';
    const IS_CLASS = 'class';
}

class Scope{
    const IS_PUBLIC = 'public';
    const IS_PROTECTED = 'protected';
    const IS_PRIVATE = 'private';
}

class Mutability{
    const IS_VARIABLE = 'variable';
    const IS_CONSTANT = 'constant';
}

class  PropertyRepresentation {

    /**
     * If set to IS_CLASS the method is in most language reputed "static"
     * @var string
     */
    public $method = Method::IS_INSTANCE;

    /**
     * @var string The scope of the variable
     */
    public $scope = Scope::IS_PUBLIC;

    /**
     * @var string The mutability of the property
     */
    public $mutability = Mutability::IS_VARIABLE;

    /**
     * @var string unique Name of the property
     */
    public $name = NULL;

    /**
     * @var string  Documentation  of the property
     */
    public $description = NULL;
    
    /**
     * @var string Type  const enumerated in FlexTypes
     */
    public $type = NULL;

    /**
     * @var  string When the type is an OBJECT or a COLLECTION or an ENUM, you can specify its class
     */
    public $instanceOf = NULL;

    /**
     *  When the type is an ENUM, you can specify its precise type.
     * Swift enum can be typed. We want to be able to cast the enums.
     * E.g : property status type=enum, instanceOf=string , enumPreciseType=User.status
     *
     * @var  string
     */
    public $emumPreciseType = NULL;

    /**
     * When $type is an ENUM you can enumerate the values in an array
     * @var array
     */
    public $enumerations = array();


    /**
     * Set to true if the type is generated (allow to to discriminate primitive from generated types)
     * @var bool
     */
    public $isGeneratedType = false;


    /**
     * Whether or not the property is required
     * in Swift for example a required property without default value must be instantiated in the constructor
     * @var  bool
     */
    public $required = false;

    /**
     * Default value to use if no value is supplied
     * @var  mixed
     */
    public $default = NULL;

    /**
     * When the type is a string, you can specify the regex pattern that a value must match
     * @var string
     */
    public $pattern = NULL;


    /**
     * Defines if the property should be serialized.
     * @var bool
     */
    public $isSerializable = true;

    /**
     * Defines if the property changes should be supervised
     * @var bool
     */
    public $isSupervisable = true;


    /**
     * Defines if the property should be crypted on Serialization
     * @var bool
     */
    public $isCryptable = false;


    /**
     * Defines if the class exists and is external to the generative package.
     * @var bool
     */
    public $isExternal = false;


    /**
     * @var bool set to false if the property is not extractible in a sub-graph copy operation
     */
    public $isExtractible = true;

    /**
     * Set to true to allow for example cocoa bindings.
     * @var bool
     */
    public $isDynamic = true;

    /**
     * @var NULL or an  array of PropertyRepresentation used to propose a serialization re-mapping
     * Usage sample : in cuds.swift.php to deal with Operation(s) partial graph mapping
     */
    public $customSerializationMapping = NULL;

    /**
     * An associative array to pass specific metadata
     * @var array
     */
    public $metadata = array();

}

?>