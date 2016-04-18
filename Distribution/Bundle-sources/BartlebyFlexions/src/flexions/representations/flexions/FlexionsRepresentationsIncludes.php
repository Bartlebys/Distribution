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


if (!defined('DEFAULT_USE_URD_MODE')) {

    // METADATA

    // Keys
    define('METADATA_KEY_FOR_USE_URD_MODE','urdMode');
    define('METADATA_KEY_FOR_IS_UNDOABLE','undoable');
    define('METADATA_KEY_FOR_PERSISTS_LOCALLY_ONLY_IN_MEMORY','persistsLocallyOnlyInMemory');
    define('METADATA_KEY_FOR_DISTANT_PERSISTENCY_IS_ALLOWED','allowDistantPersistency');


    
    // Default Values
    define('DEFAULT_USE_URD_MODE',false);
    define('DEFAULT_IS_UNDOABLE',true);
    define('DEFAULT_PERSISTS_LOCALLY_ONLY_IN_MEMORY',false);
    define('DEFAULT_DISTANT_PERSISTENCY_IS_ALLOWED',true);


    // Will be DEPRECATED but still used by XCDDataXMLToFlexionsRepresentation
    define('DEFAULT_GENERATE_COLLECTION_CLASSES',false);

}

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/ProjectRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/ActionRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/EntityRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/PropertyRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/SecurityContextRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/PermissionRepresentation.php';
require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsTypes.php';