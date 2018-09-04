<?php

namespace Beyondcode\NovaInstaller;

use FunctionParser\FunctionParser;
use ReflectionClass;

class ServiceProviderManupilator
{
    public function manipulate($classname)
    {
        $reflector = new ReflectionClass($classname);
        $serviceProvider = file_get_contents($reflector->getFileName());

        // wip

        file_put_contents($reflector->getFileName(), $serviceProvider);
    }
}