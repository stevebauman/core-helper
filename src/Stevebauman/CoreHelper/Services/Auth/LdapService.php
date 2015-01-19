<?php

namespace Stevebauman\CoreHelper\Services\Auth;

use Stevebauman\Corp\Facades\Corp;

/**
 * Class LdapService
 * @package Stevebauman\CoreHelper\Services\Auth
 */
class LdapService
{

    /**
     * @var Corp
     */
    protected $corp;

    /**
     * @param Corp $corp
     */
    public function __construct(Corp $corp)
    {
        $this->corp = $corp;
    }

    /**
     * Authenticate with Corp
     *
     * @author Steve Bauman
     *
     * @param $username , $password
     * @return boolean
     */
    public function authenticate($username, $password)
    {
        if ($this->corp->auth($username, $password)) {
            return true;
        }

        return false;
    }

    /**
     * Returns an array of all users in the current LDAP connection
     *
     * @return array
     */
    public function users()
    {
        return $this->corp->users();
    }

    /**
     * Returns Corp user object
     *
     * @param string $username
     * @return object
     */
    public function user($username)
    {
        return $this->corp->user($username);
    }

    /**
     * Return an LDAP user email address
     *
     * @author Steve Bauman
     *
     * @param $username
     * @return mixed
     */
    public function getUserEmail($username)
    {
        $user = $this->user($username);

        if ($user) {
            return $user->email;
        }

        return false;
    }

    /**
     * Return an LDAP user full name
     *
     * @author Steve Bauman
     *
     * @param $username
     * @return mixed
     */
    public function getUserFullName($username)
    {
        $user = $this->user($username);

        if ($user) {
            return $user->name;
        }

        return false;
    }
}