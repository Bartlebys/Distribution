<?php

namespace Bartleby\Core;

/*
 * The interface of any model that can be authentified
 */
interface IAuthentified{

    /**
     * @return array|null
     */
    public function getCurrentUser();

    /**
     * @param array $current_user
     */
    public function setCurrentUser($current_user);
}