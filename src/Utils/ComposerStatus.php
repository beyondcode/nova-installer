<?php

namespace Beyondcode\NovaInstaller\Utils;

use Exception;
use Illuminate\Cache\CacheManager as Cache;

class ComposerStatus
{
    /**
     * Cached object name.
     *
     * @var string
     */
    protected $cacheName = 'nova-installer';

    /**
     * The cache implementation.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * The duration of cache validity.
     *
     * @var int
     */
    protected $duration;

    /**
     * Create a new composer status object.
     *
     * @param  \Illuminate\Cache\CacheManage $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        $this->duration = 10;
    }

    /**
     * Log a message into the status.
     *
     * @param  string $message
     * @return void
     */
    public function log($message = null)
    {
        $this->set('log', $this->get('log').$message);
    }

    /**
     * Mark the installation process as started.
     *
     * @param  string $package
     * @param  string $packageName
     * @return void
     */
    public function startInstalling($package, $packageName)
    {
        $this->reset();

        $this->set($this->cacheName, [
            'is_running' => true,
            'package' => $package,
            'packageName' => $packageName,
        ]);
    }

    /**
     * Mark the installation process as started.
     *
     * @param  string $package
     * @param  string $packageName
     * @return void
     */
    public function finishInstalling($extras = [])
    {
        $this->set($this->cacheName, [
            'is_running' => false,
            'extras' => $extras,
            'finished_installation' => true,
        ]);
    }

    /**
     * Terminate the installation and log error onto status.
     *
     * @param  Exception $e
     * @return void
     */
    public function terminateForError(Exception $e)
    {
        $this->finishInstalling();
        $this->set('has_errors', true);
        $this->log($this->formatException($e));
    }

    /**
     * Return the current status of the composer installer.
     *
     * @return array
     */
    public function show()
    {
        return [
            'is_running' => $this->get('is_running', false),
            'has_errors' => $this->get('has_errors', false),
            'finished_installation' => $this->get('finished_installation', false),
            'log' => $this->get('log', null),
            'package' => $this->get('package', null),
            'packageName' => $this->get('packageName', null),
            'extras' => $this->get('extras', []),
        ];
    }

    /**
     * Bust the current composer cache.
     *
     * @return array
     */
    public function reset()
    {
        $this->cache->flush();

        return $this->show();
    }

    /**
     * Create a loggable string from the thrown exception.
     *
     * @param  Exception $e
     * @return string
     */
    protected function formatException(Exception $e)
    {
        return '<span style="color:red">****** ERROR: '.implode(', ', [$e->getFile(), $e->getLine(), $e->getMessage()]).'******</span>';
    }

    /**
     * Wrapper around the cache setter.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function set($key, $value)
    {
        if (is_bool($value) || is_string($value)) {
            $this->cache->put("{$this->cacheName}.{$key}", $value, $this->duration);
        } elseif (is_array($value)) {
            foreach ($value as $itemKey => $itemValue) {
                $this->cache->put("{$this->cacheName}.{$itemKey}", $itemValue, $this->duration);
            }
        }
    }

    /**
     * Wrapper around the cache getter.
     *
     * @param  string $key
     * @param  mixed $default
     * @return void
     */
    protected function get($key, $default = null)
    {
        return $this->cache->get("{$this->cacheName}.{$key}", $default);
    }
}
