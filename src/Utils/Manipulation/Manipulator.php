<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation;

interface Manipulator
{
    public function reflect($classname);

    public function isInstalled($provider);

    public function readFile();

    public function installProviderOfType($provider, $type);

    public function writeFile();
}
