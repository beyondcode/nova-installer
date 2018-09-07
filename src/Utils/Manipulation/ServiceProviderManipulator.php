<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation;

use Beyondcode\NovaInstaller\Utils\NovaPackagesFinder;

class ServiceProviderManipulator
{
    protected $newPackage;
    protected $manipulator;
    protected $finder;

    public function __construct(Manipulator $manipulator, NovaPackagesFinder $finder)
    {
        $this->manipulator = $manipulator;
        $this->finder = $finder;
    }

    public function setPackage($newPackage)
    {
        $this->newPackage = $newPackage;
    }

    public function addTo($classname)
    {
        $installables = $this->getInstallables();

        if (count($installables)) {
            $this->manipulator->reflect($classname);

            foreach ($installables as $installable) {
                $this->manipulator->readFile();
                $this->manipulator->parseAst();

                if (! $this->manipulator->isInstalled($installable['provider'])) {
                    $this->manipulator->installProviderOfType($installable['provider'], $installable['type']);

                    $this->manipulator->writeFile();
                }
            }
        }
    }

    public function removeFrom($classname)
    {
        $installables = $this->getInstallables();

        if (count($installables)) {
            $this->manipulator->reflect($classname);

            foreach ($installables as $installable) {
                $this->manipulator->readFile();

                if ($this->manipulator->isInstalled($installable['provider'])) {
                    $this->manipulator->removeProvider($installable['provider']);

                    $this->manipulator->writeFile();
                }
            }
        }
    }

    protected function getInstallables()
    {
        $newPackageConfig = $this->finder->getConfig($this->newPackage);

        return (isset($newPackageConfig['extra']['nova']['install'])) ? $newPackageConfig['extra']['nova']['install'] : [];
    }
}
