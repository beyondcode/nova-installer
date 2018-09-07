<?php

namespace Beyondcode\NovaInstaller\Utils;

use Beyondcode\NovaInstaller\Composer;

class PackageAction
{
    protected $type;
    protected $package;
    protected $packageName;
    protected $url;
    protected $cookies;

    protected $status;
    protected $toolsManager;
    protected $composer;

    protected $tools;

    protected $beforeCallable;
    protected $afterCallable;

    public function __construct(ComposerStatus $status, NovaToolsManager $toolsManager, Composer $composer)
    {
        $this->status = $status;
        $this->toolsManager = $toolsManager;
        $this->composer = $composer;
    }

    public function setup($type, $package, $packageName, $url, $cookies)
    {
        $this->type = $type;
        $this->package = $package;
        $this->packageName = $packageName;
        $this->url = $url;
        $this->cookies = $cookies;

        return $this;
    }

    public function run()
    {
        try {
            $this->status->startInstalling($this->package, $this->packageName);

            $this->tools = $this->toolsManager->setPackage($this->package)->getCurrentTools();

            $this->runCallable($this->beforeCallable, $this->toolsManager, $this->tools);

            $result = $this->composer->{$this->type}($this->package, function ($type, $data) {
                $this->status->log($data);
            });

            $this->throwExceptionIfInvalid($result);

            $this->runCallable($this->afterCallable, $this->toolsManager, $this->tools);

            $this->status->finishInstalling(
                $this->toolsManager->getNewToolsScriptsAndStyles($this->url, $this->cookies, $this->tools)
            );
        } catch (\Exception $e) {
            $this->status->terminateForError($e);
        }
    }

    public function before(callable $before)
    {
        $this->beforeCallable = $before;

        return $this;
    }

    public function after(callable $after)
    {
        $this->afterCallable = $after;

        return $this;
    }

    protected function runCallable(callable $callback = null, ...$args)
    {
        if ($callback) {
            call_user_func($callback, ...$args);
        }
    }

    public function throwExceptionIfInvalid($value)
    {
        if (! $value) {
            throw new \Exception("The package could not be {$this->type}ed");
        }
    }
}
