<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation;

interface Manipulator
{
    public function reflect($classname);

    public function readFile();

    public function writeFile();

    public function installProviderOfType($provider, $type);

    public function isInstalled($provider);
}
