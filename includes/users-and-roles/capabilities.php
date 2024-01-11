<?php

/**
 * @since 1.1.2
 */

namespace MPHB\Addons\Contract\UsersAndRoles;

class Capabilities
{
    const GENERATE_CONTRACTS = 'mphb_contracts_generate';

    /**
     * @var string
     */
    public $capabilities;

    /**
     * @var string
     */
    public $roles;

    /**
     * 
     * @return array
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * 
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
