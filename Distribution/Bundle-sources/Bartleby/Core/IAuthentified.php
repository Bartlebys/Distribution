<?php

namespace Bartleby\Core;

/**
 * Interface IAuthentified
 * @package Bartleby\Core
 */
interface IAuthentified{

    /**
     * @return array|null
     */
    public function getCurrentUser();

    /**
     * @param array $currentUser
     */
    public function setCurrentUser($currentUser);

}

/**
 * Interface IAuthenticationControl
 * Optionnal Control layer.
 * @package Bartleby\Core
 */
interface IAuthenticationControl{

    /**
     * Should return true if there are traces of a previous valid identification (KVIds or cookie)
     * This method does not garantee the validity of the authentication.
     * Call authenticationIsValid($spaceUID) to perform a complete verification at a given time.
     * @param $spaceUID
     * @return mixed
     */
    public function isAuthenticated($spaceUID);

    /**
     * Verifies if the authentication is still valid at a given momentum.
     * @param $spaceUID
     * @return mixed
     */
    public function authenticationIsValid ($spaceUID);

}