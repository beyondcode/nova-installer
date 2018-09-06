<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation;

use Beyondcode\NovaInstaller\Utils\NovaPackagesFinder;

class ServiceProviderManipulator
{
    protected $newPackage;
    protected $manipulator;

    public function __construct(Manipulator $manipulator)
    {
        $this->manipulator = $manipulator;
    }

    public function setPackage($newPackage)
    {
        $this->newPackage = $newPackage;
    }

    public function manipulate($classname)
    {
        $installables = $this->getInstallables();

        if (count($installables)) {
            $this->manipulator->reflect($classname);

            foreach ($installables as $installable) {
                $this->manipulator->readFile();

                if (! $this->manipulator->isInstalled($installable['provider'])) {
                    $this->manipulator->installProviderOfType($installable['provider'], $installable['type']);

                    $this->manipulator->writeFile();
                }
            }
        }
    }

    protected function getInstallables()
    {
        $newPackageConfig = (new NovaPackagesFinder)->getConfig($this->newPackage);

        return (isset($newPackageConfig['extra']['nova']['install'])) ? $newPackageConfig['extra']['nova']['install'] : [];
    }
}
