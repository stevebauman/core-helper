<?php

namespace Stevebauman\CoreHelper\Controllers;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class AbstractController
 * @package Stevebauman\CoreHelper\Controllers
 */
abstract class Controller extends BaseController
{
    /**
     * Stores the URL to redirect to
     *
     * @var string
     */
    protected $redirect;

    /**
     * Stores the message to display to the user
     *
     * @var string
     */
    protected $message;

    /**
     * Stores the type of message that is displayed to the user
     *
     * @var string
     */
    protected $messageType;

    /**
     * Holds validator errors, either array or json string
     *
     * @var array|string
     */
    protected $errors;

    /**
     * Asks the request if it's ajax or not
     *
     * @return bool
     */
    public function isAjax()
    {
        return Request::ajax();
    }

    /**
     * Returns the proper response to user. If the request was made from ajax, then an json response is sent.
     * If a request is a typical request without ajax, a user is sent a redirect with session flash messages
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function response()
    {
        if ($this->isAjax()) {
            if ($this->errors) {
                return $this->responseJson([
                    'errors' => $this->errors,
                ]);
            } else {
                return $this->responseJson([
                    'message' => $this->message,
                    'messageType' => $this->messageType,
                    'redirect' => $this->redirect
                ]);
            }
        } else {
            if ($this->errors) {
                return redirect($this->redirect)
                    ->withInput()
                    ->withErrors($this->errors);
            } else {
                return redirect($this->redirect)
                    ->withInput()
                    ->with('message', $this->message)
                    ->with('messageType', $this->messageType);
            }
        }
    }

    /**
     * Returns a JSON response to the client
     *
     * @param array $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseJson($data)
    {
        return Response::json($data);
    }

    /**
     * Returns input from the client. If clean is set to true, the input will be
     * ran through the purifier before it is returned.
     *
     * @param string $name
     * @param boolean $clean
     *
     * @return mixed
     */
    protected function input($name, $clean = FALSE)
    {
        if ($this->inputHas($name)) {
            if ($clean) {
                return $this->clean(Input::get($name));
            } else {
                return Input::get($name);
            }
        }

        return NULL;
    }

    /**
     * Returns the specified uploaded file by it's input name.
     *
     * @param string $name
     *
     * @return bool|array|UploadedFile
     */
    protected function inputFile($name)
    {
        if($this->inputHasFile($name)) {
            return Input::file($name);
        }

        return false;
    }

    /**
     * Returns all input
     *
     * @return array
     */
    protected function inputAll()
    {
        return Input::all();
    }

    /**
     * Returns true / false if the current request
     * contains an input field of the specified name.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function inputHas($name)
    {
        return Input::has($name);
    }

    /**
     * Returns true / false if the current request
     * contains an uploaded file field with the
     * specified name.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function inputHasFile($name)
    {
        return Input::hasFile($name);
    }
}
