<?php

namespace Beyondcode\NovaInstaller\Utils;

use Illuminate\Cache\CacheManager as Cache;

class ComposerStatus
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function log($message = null)
    {
        $this->cache->put('nova-installer.log', cache('nova-installer.log') . $message, 10);

        return $this->show();
    }

    public function startInstalling($package, $packageName, $key = null)
    {
        $this->reset();

        $this->cache->put('nova-installer.is_running', true, 10);
        $this->cache->put('nova-installer.has_errors', false, 10);
        $this->cache->put('nova-installer.finished_installation', false, 10);
        $this->cache->put('nova-installer.log', null, 10);
        $this->cache->put('nova-installer.package', $package, 10);
        $this->cache->put('nova-installer.packageName', $packageName, 10);
        $this->cache->put('nova-installer.needs_configuration', true, 10);

        return $this->show();
    }

    public function finishInstalling($extras = [])
    {
        $this->cache->put('nova-installer.is_running', false, 10);
        $this->cache->put('nova-installer.has_errors', false, 10);
        $this->cache->put('nova-installer.needs_configuration', true, 10);
        $this->cache->put('nova-installer.extras', $extras, 10);
        $this->cache->put('nova-installer.finished_installation', true, 10);

        return $this->show();
    }

    public function terminateForError($message)
    {
        $this->finishInstalling();
        $this->cache->put('nova-installer.has_errors', true, 10);
        $this->log($message);

        return $this->show();
    }

    public function show()
    {
        return [
            'is_running' => $this->cache->get('nova-installer.is_running', false),
            'has_errors' => $this->cache->get('nova-installer.has_errors', false),
            'finished_installation' => $this->cache->get('nova-installer.finished_installation', false),
            'log' => $this->cache->get('nova-installer.log', null),
            'package' => $this->cache->get('nova-installer.package', null),
            'packageName' => $this->cache->get('nova-installer.packageName', null),
            'needs_configuration' => $this->cache->get('nova-installer.needs_configuration', false),
            'extras' => $this->cache->get('nova-installer.extras', []),
        ];
    }

    public function reset()
    {
        $this->cache->put('nova-installer.is_running', false, 10);
        $this->cache->put('nova-installer.has_errors', false, 10);
        $this->cache->put('nova-installer.finished_installation', false, 10);
        $this->cache->put('nova-installer.package', null, 10);
        $this->cache->put('nova-installer.packageName', null, 10);
        $this->cache->put('nova-installer.needs_configuration', false, 10);
        $this->cache->put('nova-installer.extras', [], 10);
        $this->cache->put('nova-installer.log', null, 10);

        return $this->show();
    }
}
