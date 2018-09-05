<?php

namespace Beyondcode\NovaInstaller\Jobs;

use Illuminate\Bus\Queueable;
use Beyondcode\NovaInstaller\Composer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Beyondcode\NovaInstaller\Utils\ComposerStatus;
use Beyondcode\NovaInstaller\Utils\NovaToolsManager;

class InstallPackage implements ShouldQueue
{
    protected $package;
    protected $packageName;
    protected $url;
    protected $cookies;

    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($package, $packageName, $url, $cookies)
    {
        $this->package = $package;
        $this->packageName = $packageName;
        $this->url = $url;
        $this->cookies = $cookies;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ComposerStatus $status, NovaToolsManager $toolsManager, Composer $composer)
    {
        try {
            $status->startInstalling($this->package, $this->packageName);

            $toolsManager->setPackage($this->package);

            $tools = $toolsManager->getCurrentTools();

            $composer->install($this->package, function ($type, $data) use ($status) {
                $status->log($data);
            });

            $status->finishInstalling(
                $toolsManager->getNewToolsScriptsAndStyles(
                    $this->url,
                    $this->cookies,
                    $tools
                )
            );
        } catch (\Exception $e) {
            $status->terminateForError($e);
        }
    }
}
