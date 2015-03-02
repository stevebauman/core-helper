<?php

namespace Stevebauman\CoreHelper\Services;

use Illuminate\Filesystem\Filesystem;

/**
 * Class StorageService
 * @package Stevebauman\CoreHelper\Services
 */
class StorageService extends Service
{
    /**
     * Holds the laravel filesystem instance
     *
     * @var Filesystem
     */
    protected $storage;

    /**
     * Holds the folder name to store the files under
     *
     * @var string
     */
    protected $storageFolder = '';

    /**
     * Holds the configuration service
     *
     * @var ConfigService
     */
    protected $config;

    /**
     * @param Filesystem $storage
     * @param ConfigService $config
     */
    public function __construct(Filesystem $storage, ConfigService $config)
    {
        $this->storage = $storage;
        $this->config = $config->setPrefix('core-helper');

        $this->setStorageFolder($this->config->get('base-upload-path'));
    }

    /**
     * @param $path
     * @return bool
     */
    public function exists($path)
    {
        return $this->storage->exists($this->storageFolder.$path);
    }

    /**
     * @param $path
     * @return string
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    public function get($path)
    {
        return $this->storage->get($this->storagePath($path));
    }

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function upload($path, $target)
    {
        return $this->copy($path, $target);
    }

    /**
     * Copies a file from the specified path to the other
     *
     * @param $fromPath
     * @param $toPath
     * @return bool
     */
    public function copy($fromPath, $toPath)
    {
        return $this->storage->copy($this->storagePath($fromPath), $this->storagePath($toPath));
    }

    /**
     * Moves a file from the specified path to the other
     *
     * @param $fromPath
     * @param $toPath
     * @return bool
     */
    public function move($fromPath, $toPath)
    {
        return $this->storage->move($this->storagePath($fromPath), $this->storagePath($toPath));
    }

    /**
     * @param $path
     * @param $target
     * @return bool
     */
    public function download($path, $target)
    {
        return $this->storage->copy($this->storagePath($path), $target);
    }

    /**
     * Returns the type of file by the specified path
     *
     * @param $path
     * @return string
     */
    public function type($path)
    {
        return $this->storage->type($this->storagePath($path));
    }

    /**
     * Deletes a file by the specified path
     *
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        return $this->storage->delete($this->storagePath($path));
    }

    /**
     * Deletes a folder by the specified directory
     *
     * @param $path
     * @return bool
     */
    public function deleteDirectory($path)
    {
        /*
         * Make sure no files exist inside before deleting the directory
         */
        if(count($this->files($path)) === 0)
        {
            return $this->storage->deleteDirectory($this->storagePath($path));
        }

        return false;
    }

    /**
     * Returns boolean on whether or not the path specified is a directory
     *
     * @param $path
     * @return bool
     */
    public function isDirectory($path)
    {
        return $this->storage->isDirectory($this->storagePath($path));
    }

    /**
     * @param $path
     * @return array
     */
    public function files($path)
    {
        return $this->storage->files($this->storagePath($path));
    }

    /**
     * @param $path
     * @return string
     */
    public function mime($path)
    {
        $fileInfo = new \Finfo(FILEINFO_MIME_TYPE);

        return $fileInfo->file($this->storagePath($path));
    }

    /**
     * @param $path
     * @return int
     */
    public function size($path)
    {
        return $this->storage->size($this->storagePath($path));
    }

    /**
     * @param $path
     * @return int
     */
    public function lastModified($path)
    {
        return $this->storage->lastModified($this->storagePath($path));
    }

    /**
     * Returns a URL for the specified file
     *
     * @param $path
     * @return string
     */
    public function url($path)
    {
        return url($this->storagePath($path));
    }

    /**
     * Returns a path relative to the set storage folder
     *
     * @param $path
     * @return string
     */
    public function storagePath($path)
    {
        return $this->storageFolder . $path;
    }

    /**
     * Sets the folder for storing maintenance files under
     *
     * @param string $folder
     * @return $this
     */
    public function setStorageFolder($folder = '')
    {
        $this->storageFolder = public_path($folder);

        $this->verifyDirectory($this->storageFolder);

        return $this;
    }

    /**
     * Verifies that the directory in the specified path exists,
     * if it does not exist, it will create it
     *
     * @param $path
     */
    private function verifyDirectory($path)
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        unset($parts[count($parts)-1]);

        $path = implode(DIRECTORY_SEPARATOR, $parts);

        if (!$this->storage->isDirectory($path))
        {
            $this->storage->makeDirectory($path, 0777, true);
        }
    }
}