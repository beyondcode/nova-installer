<?php

namespace Beyondcode\NovaInstaller\Jobs;

use Beyondcode\NovaInstaller\Utils\PackageAction;

interface PackageJobInterface
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($package, $packageName, $url, $cookies);

    /**
     * Execute the package installation.
     *
     * @return void
     */
    public function handle(PackageAction $action);
}
