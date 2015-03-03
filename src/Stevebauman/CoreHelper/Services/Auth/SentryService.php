<?php

namespace Stevebauman\CoreHelper\Services\Auth;

use Cartalyst\Sentry\Facades\Laravel\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\WrongPasswordException;
use Cartalyst\Sentry\Users\UserNotActivatedException;
use Cartalyst\Sentry\Throttling\UserSuspendedException;
use Cartalyst\Sentry\Throttling\UserBannedException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;

/**
 * Class SentryService
 * @package Stevebauman\CoreHelper\Services\Auth
 */
class SentryService
{
    /**
     * Authenticate with Sentry
     *
     * @param $credentials
     * @param null $remember
     * @return array
     */
    public function authenticate($credentials, $remember = NULL)
    {
        $response = array(
            'authenticated' => false,
            'message' => '',
        );

        /*
         * Try to log in the user with sentry
         */
        try
        {
            Sentry::authenticate($credentials, $remember);

            $response['authenticated'] = true;

            /*
             * Credentials were valid, return authenticated response
             */
            return $response;

        } catch (WrongPasswordException $e)
        {
            $response['message'] = 'Username or Password is incorrect.';
        } catch (UserNotActivatedException $e)
        {
            $response['message'] = 'Your account has not been activated.
                Please follow the link you were emailed to activate your account.';
        } catch (UserSuspendedException $e)
        {
            $response['message'] = 'Your account has been suspended. Please try again later.';
        } catch (UserBannedException $e)
        {
            $response['message'] = 'Your account has been permanently banned.';
        } catch (UserExistsException $e)
        {
            $response['message'] = 'Username or Password is incorrect.';
        } catch (UserNotFoundException $e)
        {
            $response['message'] = 'Username or Password is incorrect.';
        }

        return $response;
    }

    /**
     * Logout with Sentry
     *
     * @return void
     */
    public function logout()
    {
        Sentry::logout();
    }

    /**
     * Create a user through Sentry and add the groups specified to the user
     * if they exist
     *
     * @param $data
     * @param null $groups
     * @return mixed
     */
    public function createUser($data, $groups = NULL)
    {
        try
        {
            $user = Sentry::getUserProvider()->create($data);

            if (isset($groups))
            {
                foreach ($groups as $group)
                {
                    try
                    {
                        $group = Sentry::findGroupByName($group);

                        $user->addGroup($group);

                    } catch (GroupNotFoundException $e)
                    {

                    }
                }

            }
        } catch (UserExistsException $e)
        {
            $login_attribute = config('cartalyst/sentry::users.login_attribute');

            $user = Sentry::findUserByLogin($data[$login_attribute]);
        }

        return $user;
    }

    /**
     * Create or update a group through Sentry
     *
     * If the permissions array is empty it will leave the current permissions intact.
     *
     * @param string $name The name for the group to find or create
     * @param array $permissions The permissions to assign the group.
     * @return mixed
     */
    public function createOrUpdateGroup($name, $permissions = array())
    {
        try
        {
            /*
             * Group already exists, lets try and update the permissions
             * if we were supplied any
             */
            $group = Sentry::findGroupByName($name);

            if (!empty($permissions))
            {
                $group->permissions = $permissions;
                $group->save();
            }

        } catch (GroupNotFoundException $e)
        {
            /*
             * If the group does not exist, we'll create it and assign
             * the permissions
             */
            $group = Sentry::createGroup(array(
                'name' => $name,
                'permissions' => $permissions,
            ));
        }

        return $group;
    }

    /**
     * Update a user password through sentry
     *
     * @param $id
     * @param $password
     * @return bool
     */
    public function updatePasswordById($id, $password)
    {
        $user = $this->findUserById($id);

        $user->password = $password;

        if ($user->save()) return true;

        return false;
    }

    /**
     * Updates a user through Sentry
     *
     * @param $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data = array())
    {
        try
        {
            $user = Sentry::findUserById($id);

            if($user->update($data))
            {
                return $user;
            }
        } catch (UserExistsException $e)
        {

        } catch(UserNotFoundException $e)
        {

        }

        return false;
    }

    /**
     * Find a user through Sentry
     *
     * @param $id
     * @return bool
     */
    public function findUserById($id)
    {
        try
        {
            $user = Sentry::findUserById($id);

            return $user;
        } catch (UserNotFoundException $e)
        {
            return false;
        }
    }

    /**
     * Returns current authenticated user
     *
     * @return mixed
     */
    public function getCurrentUser()
    {
        return Sentry::getUser();
    }

    /**
     * Returns current authenticated users full name
     *
     * @return string
     */
    public function getCurrentUserFullName()
    {
        $user = Sentry::getUser();

        $fullName = sprintf('%s %s', $user->first_name, $user->last_name);

        return $fullName;
    }

    /**
     * Returns current authenticated user ID
     *
     * @return mixed
     */
    public function getCurrentUserId()
    {
        $user = Sentry::getUser();

        return $user->id;
    }
}