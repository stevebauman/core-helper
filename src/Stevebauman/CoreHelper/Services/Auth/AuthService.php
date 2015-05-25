<?php

namespace Stevebauman\CoreHelper\Services\Auth;

/**
 * Class AuthService
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
     * Constructor.
     *
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
     * @param array $credentials
     *
     * @return bool
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
     * @param array $credentials
     * @param bool $remember
     *
     * @return array
     */
    public function sentryAuthenticate(array $credentials, $remember = false)
    {
        return $this->sentry->authenticate($credentials, $remember);
    }

    /**
     * Logout with Sentry,
     *
     * @return bool
     */
    public function sentryLogout()
    {
        return $this->sentry->logout();
    }
}
