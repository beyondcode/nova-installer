<?php

namespace Beyondcode\NovaInstaller\Jobs;

use Illuminate\Bus\Queueable;
use Beyondcode\NovaInstaller\Composer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Beyondcode\NovaInstaller\Utils\PackageAction;

class UpdatePackage implements ShouldQueue, PackageJobInterface
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
    public function handle(PackageAction $action)
    {
        $action->setup('update', $this->package, $this->packageName, $this->url, $this->cookies);

        $action->after(function ($toolsManager) {
            $toolsManager->registerTools();
        });

        $action->run();
    }
}
