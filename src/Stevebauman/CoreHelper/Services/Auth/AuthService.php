<?php

namespace Stevebauman\CoreHelper\Services\Auth;

use Stevebauman\CoreHelper\Services\ConfigService;

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
     * @var ConfigService
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param LdapService   $ldap
     * @param SentryService $sentry
     * @param ConfigService $config
     */
    public function __construct(LdapService $ldap, SentryService $sentry, ConfigService $config)
    {
        $this->ldap = $ldap;
        $this->sentry = $sentry;
        $this->config = $config;
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
        $loginAttribute = $this->config->setPrefix('cartalyst.sentry')->get('users.login_attribute');

        if(array_key_exists($loginAttribute, $credentials)) {
            if ($this->ldap->authenticate($credentials[$loginAttribute], $credentials['password']))
            {
                return true;
            }
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
