<?php

namespace Beyondcode\NovaInstaller\Console\Commands;

use Illuminate\Console\Command;
use Beyondcode\NovaInstaller\Utils\NovaToolsManager;
use Beyondcode\NovaInstaller\Utils\NovaPackagesFinder;

class NovaPackageDiscoverCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'nova:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-register the Nova Service Provider';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Foundation\PackageManifest  $manifest
     * @return void
     */
    public function handle(NovaPackagesFinder $finder, NovaToolsManager $toolsManager)
    {
        $packages = $finder->all();

        foreach ($packages as $package) {
            $toolsManager->setPackage($package['name']);
            $toolsManager->registerTools();
            $this->line("Discovered Nova Package: <info>{$package['name']}</info>");
        }

        $this->info('Nova Service Provider rebuilt successfully.');
    }
}
