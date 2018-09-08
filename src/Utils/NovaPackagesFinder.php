<?php

namespace Beyondcode\NovaInstaller\Utils;

use Illuminate\Foundation\PackageManifest;

class NovaPackagesFinder
{

    /**
     * Laravel's package manifest.
     *
     * @var Illuminate\Foundation\PackageManifest
     */

    protected $manifest;


    /**
     * The fields that will be extracetd from packages.
     *
     * @var array
     */

    protected $fields = [
        'name',
        'description',
        'keywords',
        'version',
        'authors',
        'type',
        'extra',
    ];


    /**
     * The matching keyword to parse packages for.
     *
     * @var string
     */

    protected $keyword = 'nova';


    /**
     * Create a new package findder object.
     *
     * @param  Illuminate\Foundation\PackageManifest $manifest
     * @return void
     */

    public function __construct(PackageManifest $manifest)
    {
        $this->manifest = $manifest;
    }


    /**
     * Get configuration object from the composer.json of a given package.
     *
     * @param  string $package
     * @return Illuminate\Support\Collection
     */

    public function getConfig($package)
    {
        $packageConfig = [];

        if ($this->manifest->files->exists($path = $this->manifest->vendorPath.'/'.$package.'/composer.json')) {
            $packageConfig = json_decode($this->manifest->files->get($path), true);
        }

        return collect($packageConfig);
    }


    /**
     * Return currently installed nova-related packages.
     *
     * @return array
     */

    public function all()
    {
        return $this->getAllInstalledPackages()->map(function ($package) {
            return $this->extractFieldsOfInterest($package);
        })->filter(function ($package) {
            return $this->keepOnlyNovaPackages($package);
        })->map(function ($package) {
            return $this->compactAuthorNames($package);
        })->toArray();
    }


    /**
    * Return currently installed nova-related packages.
    *
    * @return Illuminate\Support\Collection
    */

    protected function getAllInstalledPackages()
    {
        $packages = [];

        if ($this->manifest->files->exists($path = $this->manifest->vendorPath.'/composer/installed.json')) {
            $packages = json_decode($this->manifest->files->get($path), true);
        }

        return collect($packages);
    }


    /**
    * Extract only fileds contained in $fields.
    *
    * @return Illuminate\Support\Collection
    */

    protected function extractFieldsOfInterest($package)
    {
        return collect($package)->only($this->fields);
    }


    /**
    * Evaluate wether a package contains the required keyword.
    *
    * @return bool
    */

    protected function keepOnlyNovaPackages($package)
    {
        return in_array($this->keyword, ($package['keywords']) ?? []);
    }

    /**
    * Compact multiple author names in string format
    *
    * @return Illuminate\Support\Collection
    */

    protected function compactAuthorNames($package)
    {
        if (isset($package['authors'])) {
            $package['authors'] = collect($package['authors'])->map(function ($author) {
                return $author['name'];
            })->implode(', ');
        }

        return $package;
    }
}
