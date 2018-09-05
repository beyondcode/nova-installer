<?php

namespace Beyondcode\NovaInstaller\Utils;

use Composed;

class NovaPackagesFinder
{
    protected $fields = [
        'name',
        'description',
        'keywords',
        'version',
        'authors',
        'type',
        'extra'
    ];

    protected $keyword = 'nova';

    public function getExtraForPackage($package)
    {
        return collect(Composed\package($package)->getConfig());
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
        return collect(Composed\packages());
    }

    private function extractFieldsOfInterest($package)
    {
        return collect($package->getConfig())->only($this->fields);
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
