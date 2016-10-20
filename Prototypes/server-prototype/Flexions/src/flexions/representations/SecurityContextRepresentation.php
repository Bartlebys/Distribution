<?php

require_once FLEXIONS_ROOT_DIR . '/flexions/core/Enum.php';

class RelationToPermission extends Enum {

    const UNDEFINED = 'undefined';
    const REQUIRES = 'requires';  // authentication required
    const PROVIDES = 'provides';  // e.g log in
    const DISCARDS = 'discards';  // e.g log out

    static function possibleValues() {
        return array(
            RelationToPermission::UNDEFINED,
            RelationToPermission::REQUIRES,
            RelationToPermission::PROVIDES,
            RelationToPermission::DISCARDS
        );
    }

}


class SecurityContextRepresentation {

    /**
     * @var PermissionRepresentation
     */
    private $permission;

    /**
     * @var string one of RelationToPermission consts
     */
    private $_relation = RelationToPermission::UNDEFINED;


    /**
     * @return PermissionRepresentation
     */
    public function getPermission() {
        return $this->permission;
    }

    /**
     * @param PermissionRepresentation $permission
     */
    public function setPermission(PermissionRepresentation $permission) {
        $this->permission = $permission;
    }


    /**
     * @return string
     */
    public function getRelation() {
        return $this->_relation;
    }

    /**
     * @param string $relation
     */
    public function setRelation($relation) {
        $this->_relation = $relation;
    }

}
