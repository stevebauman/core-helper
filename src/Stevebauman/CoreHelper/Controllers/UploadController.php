<?php 

namespace Stevebauman\CoreHelper\Controllers;

use Illuminate\Support\Facades\View;
use Stevebauman\CoreHelper\Services\StorageService;
use Stevebauman\CoreHelper\Services\ConfigService;
use JildertMiedema\LaravelPlupload\Facades\Plupload;

/**
 * Class AbstractUploadController
 * @package Stevebauman\CoreHelper\Controllers
 */
abstract class AbstractUploadController extends Controller
{
    /**
     * @var ConfigService
     */
    protected $config;

    /**
     * @var StorageService
     */
    protected $storage;

    /**
     * Holds the final storage path for uploaded files
     *
     * @var string
     */
	protected $storagePath;

    /**
     * Holds the temporary upload path for files
     *
     * @var string
     */
	protected $uploadPath;

    /**
     * Holds the view to return with
     *
     * @var
     */
	protected $responseView;

    /**
     * @param ConfigService $config
     * @param StorageService $storage
     */
	public function __construct(ConfigService $config, StorageService $storage)
    {
        $this->config = $config->setPrefix('core-helper');
        $this->storage = $storage;

        $this->uploadPath = $this->config->get('temp-upload-path', 'temp/');
        $this->storagePath = $this->config->get('base-upload-path', 'files/');
	}

    /**
     * Stores an uploaded file into temporary storage
     *
     * @return mixed
     */
	public function store()
    {
        /*
         * Init Plupload receive
         */
        return Plupload::receive('file', function ($file)
        {
            /*
             * Assign a unique ID for the file and assign it's original extension
             */
            $fileName = sprintf('%s.%s', uniqid(), $file->getClientOriginalExtension());

            /*
             * Combine the upload path with the file name
             */
            $filePath = $this->uploadPath . $fileName;

            /*
             * Get the files URL
             */
            $url = $this->storage->url($filePath);

            /*
             * Move the file into temporary storage
             */
            if($file->move($this->storagePath.$this->uploadPath, $fileName))
            {
                /*
                 * Return ajax response with file information on successful upload
                 */
                return array(
                    'url'=>$url,
                    'name'=>$fileName,
                    'html'=>View::make($this->responseView)
                        ->with('file', $file)
                        ->with('fileName', $fileName)
                        ->with('filePath', $filePath)
                        ->with('fileFolder', $this->uploadPath)
                        ->render()
                );

            } else
            {
                $this->messageType = 'danger';
                $this->message = 'There was an error uploading your attachment';

                return $this->response();
            }
        });
	}

    /**
     * Deletes an uploaded file from temporary storage
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
	public function destroy()
    {
        $filePath = $this->input('file_path');
        $fileFolder = $this->input('file_folder');

        /*
         * Make sure sure the request is ajax only
         */
        if($this->isAjax())
        {
            /*
             * If the delete is successful
             */
            if($this->storage->delete($filePath))
            {
                /*
                 * Get the folder of the file
                 */
                $folder = $this->storagePath.$fileFolder;

                /*
                 * Delete the directory, but only if it's empty
                 */
                $this->storage->deleteDirectory($folder);

                $this->messageType = 'success';
                $this->message = 'Successfully deleted attachment';

                return $this->response();

            } else
            {
                $this->messageType = 'danger';
                $this->message = 'Error deleting attachment';

                return $this->response();
            }
        }
	}
	
}