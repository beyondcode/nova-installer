<?php

namespace Beyondcode\NovaInstaller\Utils;

use Illuminate\Foundation\PackageManifest;

class NovaPackagesFinder
{
    protected $manifest;

    protected $fields = [
        'name',
        'description',
        'keywords',
        'version',
        'authors',
        'type',
        'extra',
    ];

    public function __construct(PackageManifest $manifest)
    {
        $this->manifest = $manifest;
    }

    protected $keyword = 'nova';

    public function getConfig($package)
    {
        $packages = [];

        if ($this->manifest->files->exists($path = $this->manifest->vendorPath.'/'.$package.'/composer.json')) {
            $packages = json_decode($this->manifest->files->get($path), true);
        }

        return collect($packages);
    }

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

    private function getAllInstalledPackages()
    {
        if ($this->manifest->files->exists($path = $this->manifest->vendorPath.'/composer/installed.json')) {
            $packages = json_decode($this->manifest->files->get($path), true);
        }

        return collect($packages);
    }

    private function extractFieldsOfInterest($package)
    {
        return collect($package)->only($this->fields);
    }

    public function keepOnlyNovaPackages($package)
    {
        return in_array($this->keyword, ($package['keywords']) ?? []);
    }

    private function compactAuthorNames($package)
    {
        if (isset($package['authors'])) {
            $package['authors'] = collect($package['authors'])->map(function ($author) {
                return $author['name'];
            })->implode(', ');
        }

        return $package;
    }
}
