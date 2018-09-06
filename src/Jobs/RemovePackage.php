<?php

namespace Beyondcode\NovaInstaller\Jobs;

use Illuminate\Bus\Queueable;
use Beyondcode\NovaInstaller\Composer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Beyondcode\NovaInstaller\Utils\ComposerStatus;
use Beyondcode\NovaInstaller\Utils\NovaToolsManager;

class RemovePackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The composer name of the package.
     *
     * @var string
     */
    protected $package;

    /**
     * The human readable name of the package.
     *
     * @var string
     */
    protected $packageName;

    /**
     * The requesting url.
     *
     * @var string
     */
    protected $url;

    /**
     * The available cookies.
     *
     * @var string
     */
    protected $cookies;

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
     * Execute the package installation.
     *
     * @return void
     */
    public function handle(ComposerStatus $status, NovaToolsManager $toolsManager, Composer $composer)
    {
        try {
            $status->startInstalling($this->package, $this->packageName);

            $toolsManager->setPackage($this->package);

            $tools = $toolsManager->getCurrentTools();

            $toolsManager->unregisterTools();

            $result = $composer->remove($this->package, function ($type, $data) use ($status) {
                $status->log($data);
            });

            if (! $result) {
                throw new \Exception('The package could not be removeed');
            }

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
