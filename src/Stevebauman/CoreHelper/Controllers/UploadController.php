<?php 

namespace Stevebauman\CoreHelper\Controllers;

use Illuminate\Support\Facades\View;
use JildertMiedema\LaravelPlupload\Facades\Plupload;
use Dmyers\Storage\Storage;
use ErrorException;

/**
 * Class AbstractUploadController
 * @package Stevebauman\CoreHelper\Controllers
 */
abstract class AbstractUploadController extends Controller {
	
	protected $storagePath;
	
	protected $uploadPath;
	
	protected $responseView;
	
	public function __construct()
        {
            $this->uploadPath = config('core-helper::temp-upload-path');
            $this->storagePath = config('core-helper::base-upload-path');
	}
	
	public function store()
        {
            //Init Plupload receive
            return Plupload::receive('file', function ($file){
                
                $fileName = sprintf('%s.%s', uniqid(), $file->getClientOriginalExtension());
                $filePath = $this->uploadPath . $fileName;
                $url = Storage::url($filePath);
                
                if($file->move($this->storagePath.$this->uploadPath, $fileName)) {
                    
                    //Return ajax response with file information on successful upload
                    return array('url'=>$url, 'name'=>$fileName, 'html'=>View::make($this->responseView)
                        ->with('file', $file)
                        ->with('fileName', $fileName)
                        ->with('filePath', $filePath)
                        ->with('fileFolder', $this->uploadPath)
                        ->render());
                    
                } else {
                    $this->messageType = 'danger';
                    $this->message = 'There was an error uploading your attachment';
                    
                    return $this->response();
                }
            });
	}
	
	public function destroy()
        {
            $filePath = $this->input('file_path');
            $fileFolder = $this->input('file_folder');
            
            if($this->isAjax()){
                
                /*
                 * If the delete is successful
                 */
                if(Storage::delete($filePath)){
                    /*
                     * Get the folder of the file
                     */
                    $folder = $this->storagePath.$fileFolder;
                    
                    /*
                     * Try and remove the folder, catch the exception if it's
                     * not empty
                     */
                    try {
                        
                        rmdir($folder);
                        
                    } catch(ErrorException $e){
                        
                    }
                    
                    $this->messageType = 'success';
                    $this->message = 'Successfully deleted attachment';
                    
                    return $this->response();
                    
                } else{
                    
                    $this->messageType = 'danger';
                    $this->message = 'Error deleting attachment';
                    
                    return $this->response();
                    
                }
                
            }
	}
	
}