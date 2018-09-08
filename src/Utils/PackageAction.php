<?php

namespace Beyondcode\NovaInstaller\Utils;

class PackageAction
{

    /**
     * Type of action to perform.
     *
     * @var string
     */

    protected $type;

    /**
     * Package composer name.
     *
     * @var string
     */

    protected $package;

    /**
     * Package human-readable name.
     *
     * @var string
     */

    protected $packageName;


    /**
     * Requesting url.
     *
     * @var string
     */

    protected $url;


    /**
     * available cookies.
     *
     * @var string
     */

    protected $cookies;

    /**
     * Status manager.
     *
     * @var Beyondcode\NovaInstaller\Utils\ComposerStatus
     */

    protected $status;


    /**
     * Tools manager.
     *
     * @var Beyondcode\NovaInstaller\Utils\NovaToolsManager
     */

    protected $toolsManager;


    /**
     * Composer runner.
     *
     * @var Beyondcode\NovaInstaller\Utils\Composer
     */

    protected $composer;


    /**
     * Available tools already registered.
     *
     * @var array
     */

    protected $tools;


    /**
     * Before callable hook.
     *
     * @var callable
     */

    protected $beforeCallable;


    /**
     * After callable hook.
     *
     * @var callable
     */

    protected $afterCallable;


    /**
     * Create a new package action object.
     *
     * @param  Beyondcode\NovaInstaller\Utils\ComposerStatus $status
     * @param  Beyondcode\NovaInstaller\Utils\NovaToolsManager $toolsManager
     * @param  Beyondcode\NovaInstaller\Utils\Composer $composer
     * @return void
     */

    public function __construct(ComposerStatus $status, NovaToolsManager $toolsManager, Composer $composer)
    {
        $this->status = $status;
        $this->toolsManager = $toolsManager;
        $this->composer = $composer;
    }


    /**
     * Setup the action.
     *
     * @param  string $type
     * @param  string $package
     * @param  string $packageName
     * @param  string $url
     * @param  string $cookies
     *
     * @return Beyondcode\NovaInstaller\Utils\PackageAction
     */

    public function setup($type, $package, $packageName, $url, $cookies)
    {
        $this->type = $type;
        $this->package = $package;
        $this->packageName = $packageName;
        $this->url = $url;
        $this->cookies = $cookies;

        return $this;
    }


    /**
     * Execute the action.
     *
     * @return void
     */

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


    /**
     * Register the before hook.
     *
     * @param  callable $before
     *
     * @return void
     */

    public function before(callable $before)
    {
        $this->beforeCallable = $before;

        return $this;
    }


    /**
     * Register the after hook.
     *
     * @param  callable $after
     *
     * @return void
     */

    public function after(callable $after)
    {
        $this->afterCallable = $after;

        return $this;
    }


    /**
     * Excecute a given callback and pass through the parameters.
     *
     * @param  callable $callback
     * @param  mixed $args
     *
     * @return mixed
     */

    protected function runCallable(callable $callback = null, ...$args)
    {
        if ($callback) {
            call_user_func($callback, ...$args);
        }
    }


    /**
     * Fail process with \Exception if $value is not truthy.
     *
     * @param  mixed $value
     *
     * @return mixed
     */

    public function throwExceptionIfInvalid($value)
    {
        if (! $value) {
            throw new \Exception("The package could not be {$this->type}ed");
        }
    }
}
