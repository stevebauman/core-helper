<?php

namespace Stevebauman\CoreHelper\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;

/**
 * Class ConfigService
 *
 * @package Stevebauman\CoreHelper\Services
 */
class ConfigService extends Service
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Stores the prefix for accessing package configuration values
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Stores the prefix separator
     *
     * @var string
     */
    protected $prefixSeparator = '.';

    /**
     * @param Config $config
     * @param Filesystem $filesystem
     */
    public function __construct(Config $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    /**
     * Retrieves the specified key from the current configuration
     *
     * @param int|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = NULL)
    {
        return $this->config->get($this->prefix.$key, $default);
    }

    /**
     * Sets a configuration by the specified key
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->config->set($this->prefix.$key, $value);

        return $this;
    }

    /**
     * Sets the prefix property
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix = '')
    {
        $this->prefix = $prefix.$this->prefixSeparator;

        return $this;
    }

    /**
     * Sets the prefix separator
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setPrefixSeparator($separator = '')
    {
        $this->prefixSeparator = $separator;

        return $this;
    }

    /**
     * Replaces content from configuration files and returns the result content
     *
     * @param $content
     * @param $name
     * @param $entry
     * @param string $value
     * @param string $type
     *
     * @return mixed
     */
    protected function replaceConfigEntry($content, $name, $entry, $value = "''", $type = 'string')
    {
        switch($type)
        {
            case 'string':

                $oldEntry = sprintf("'$name' => '%s'", addslashes($this->get($entry)));
                $newEntry = sprintf("'$name' => '%s'", addslashes($value));

                return str_replace($oldEntry, $newEntry, $content);
            case 'integer':

                $oldEntry = sprintf("'$name' => %s", $this->get($entry));
                $newEntry = sprintf("'$name' => %s", $value);

                return str_replace($oldEntry, $newEntry, $content);
            case 'bool':
                $oldEntry = sprintf("'$name' => %s", var_export($this->get($entry), true));
                $newEntry = sprintf("'$name' => %s", var_export($value, true));

                return str_replace($oldEntry, $newEntry, $content);
            default:
                return $content;
        }
    }

    /**
     * Returns the contents of the specified file path.
     *
     * @param $path
     *
     * @return string
     *
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    protected function getConfigFile($path)
    {
        return $this->filesystem->get(config_path($path));
    }

    /**
     * Inserts the specified content into the config
     * file that exists in the specified path.
     *
     * @param string $path
     * @param string $content
     *
     * @return bool
     */
    protected function setConfigFile($path, $content)
    {
        if($this->filesystem->put(config_path($path), $content)) return true;

        return false;
    }
}
