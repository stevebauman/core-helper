<?php

namespace Stevebauman\CoreHelper\Services\Auth;

/**
 * Class AuthService
 * @package Stevebauman\CoreHelper\Services\Auth
 */
class AuthService
{

    /**
     * @var LdapService
     */
    protected $ldap;

    /**
     * @var SentryService
     */
    protected $sentry;

    /**
     * @param LdapService $ldap
     * @param SentryService $sentry
     */
    public function __construct(LdapService $ldap, SentryService $sentry)
    {
        $this->ldap = $ldap;
        $this->sentry = $sentry;
    }

    /**
     * Authenticate with Ldap
     *
     * @author Steve Bauman
     *
     * @param $credentials
     * @return boolean
     */
    public function ldapAuthenticate($credentials)
    {
        $loginAttribute = config('cartalyst/sentry::users.login_attribute');

        if ($this->ldap->authenticate($credentials[$loginAttribute], $credentials['password']))
        {
            return true;
        }

        return false;
    }

    /**
     * Authenticate with Sentry
     *
     * @author Steve Bauman
     *
     * @param $credentials , $remember
     * @return Array
     */
    public function sentryAuthenticate($credentials, $remember = NULL)
    {
        return $this->sentry->authenticate($credentials, $remember);
    }

    /**
     * Logout with Sentry
     *
     * @author Steve Bauman
     *
     * @return void
     */
    public function sentryLogout()
    {
        $this->sentry->logout();
    }
}