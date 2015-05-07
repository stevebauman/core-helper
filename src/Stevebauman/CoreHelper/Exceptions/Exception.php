<?php

namespace Stevebauman\CoreHelper\Exceptions;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

/**
 * Class Exception
 * @package Stevebauman\CoreHelper\Exceptions
 */
abstract class Exception extends \Exception
{
    /**
     * The exception message.
     *
     * @var string
     */
    protected $message;

    /**
     * The exception message type.
     *
     * @var string
     */
    protected $messageType;

    /**
     * The redirect URL string.
     *
     * @var string
     */
    protected $redirect;

    /**
     * Returns a suitable response from the type of request.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function response()
    {
        if(Request::ajax()) {
            return Response::json([
                    'message' => $this->message,
                    'messageType' => $this->messageType,
                    'redirect' => $this->redirect
            ]);
        } else {
            return Redirect::to($this->redirect)
                    ->with('message', $this->message)
                    ->with('messageType', $this->messageType);
        }
    }

    /**
     * Returns the specified route parameter if it exists.
     *
     * @param string $parameter
     *
     * @return bool|string|int
     */
    public function getRouteParameter($parameter)
    {
        $param = Route::getCurrentRoute()->getParameter($parameter);

        if($param) {
            return $param;
        }
        
        return false;
    }
}
