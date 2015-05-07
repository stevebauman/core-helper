<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

if (! function_exists('currentControllerAction')) {

    /**
     * Returns the current controller actions method name.
     *
     * @param string $method
     *
     * @return string
     */
    function currentControllerAction($method) {
        $class = explode('@', Route::currentRouteAction());

        return sprintf('%s@%s', $class[0], $method);
    }
}

if (! function_exists('currentRouteName')) {

    /**
     * Returns the current routes name.
     *
     * @return mixed
     */
    function currentRouteName()
    {
        return Route::currentRouteName();
    }
}

if (! function_exists('currentUrl')) {
    /**
     * Returns the current URL string.
     *
     * @return string
     */
    function currentUrl()
    {
        return Request::url();
    }
}

if (! function_exists('link_to_sort')) {

    /**
     * Returns a link to sort a table column with the query scope 'sort'
     *
     * @param string $name
     * @param string $title
     * @param array $parameters
     *
     * @return string
     */
    function link_to_sort($name, $title, $parameters) {
        $field = Input::get('field');
        $sort = Input::get('sort');

        if ($sort == 'desc') {
            $parameters['sort'] = 'asc';
        } else {
            $parameters['sort'] = 'desc';
        }

        if ($field == $parameters['field']) {
            $icon = sprintf('fa %s-%s', 'fa-sort', $parameters['sort']);
        } else {
            $icon = sprintf('fa %s', 'fa-sort');
        }

        return sprintf('<a class="link-sort" href="%s">%s <i class="%s"></i></a>', route($name, $parameters), $title, $icon);
    }
}

if (! function_exists('activeMenuLink')) {
    /**
     * Returns active class if the current sub route is inside the current
     * route name. This is used for exanding the UI navigation tree if the
     * user is on the current tree routes
     *
     * @param string $subRoute
     *
     * @return string
     */
    function activeMenuLink($subRoute = '') {
        if (str_contains(currentRouteName(), $subRoute)) {
            return 'active';
        } else {
            return NULL;
        }
    }
}

if (! function_exists('config')) {
    /**
     * Helper for config facade. Checks if config helper function already exists
     * for Laravel 5 support.
     *
     * @param string $key
     * @param string $default
     *
     * @return array|string
     */
    function config($key, $default = NULL)
    {
        return Config::get($key, $default);
    }
}

if (! function_exists('view')) {

    /**
     * Helper for view facade. Checks if view helper function already exists
     * for Laravel 5 support
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     *
     * @return \Illuminate\View\View
     */
    function view($view, $data = [], $mergeData = []) {
        return View::make($view, $data, $mergeData);
    }
}

/**
 * Helper for redirect facade. Checks if redirect helper function already exists
 * for Laravel 5 support
 */
if (! function_exists('redirect')) {

    /**
     * Returns a redirect to the specified URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function redirect($url) {
        return Redirect::to($url);
    }
}

if (!function_exists('routeBack')) {

    /**
     * Generate a URL to a named route or returns a url to the users
     * previous URL if it exists.
     *
     * @param  string $name
     * @param  array $parameters
     * @param  bool $absolute
     * @param  \Illuminate\Routing\Route $route
     *
     * @return string
     */
    function routeBack($name, $parameters = [], $absolute = true, $route = null)
    {
        if (Request::header('referer')) {
            return URL::previous();
        } else {
            return route($name, $parameters, $absolute, $route);
        }
    }
}
