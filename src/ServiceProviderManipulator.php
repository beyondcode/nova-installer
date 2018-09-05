<?php

namespace Beyondcode\NovaInstaller;

use ReflectionClass;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Beyondcode\NovaInstaller\Utils\NovaPackagesFinder;
use Beyondcode\NovaInstaller\Utils\ProviderNodeVisitor;

class ServiceProviderManipulator
{
    protected $newPackage;

    public function __construct($newPackage)
    {
        $this->newPackage = $newPackage;
    }

    public function manipulate($classname)
    {
        $reflector = new ReflectionClass($classname);

        $installables = $this->getInstallables();

        if (count($installables)) {
            foreach ($installables as $installable) {
                $serviceProvider = file_get_contents($reflector->getFileName());
                $ast = $this->parseAstOf($serviceProvider);

                if (! $this->isInstalled($installable['provider'], $serviceProvider)) {
                    $visitor = $this->makeNodeVisitorLookingForType($installable['type']);

                    $this->traverseAstWithVisitor($ast, $visitor);

                    $providerAsArray  = file($reflector->getFileName());

                    array_splice($providerAsArray, $visitor->line - 1, 0, str_repeat(" ", 12) . $installable['provider'] . ",\n");

                    file_put_contents($reflector->getFileName(), join('', $providerAsArray));
                }
            }
        }
    }

    protected function traverseAstWithVisitor($ast, $visitor)
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);

        $modifiedStmts = $traverser->traverse($ast);
    }

    protected function isInstalled($classname, $serviceProvider)
    {
        return strpos($serviceProvider, $classname);
    }

    protected function getInstallables()
    {
        $newPackageConfig = (new NovaPackagesFinder)->getExtraForPackage($this->newPackage);

        return (isset($newPackageConfig['extra']['nova']['install'])) ? $newPackageConfig['extra']['nova']['install'] : [];
    }

    protected function makeNodeVisitorLookingForType($type)
    {
        return new ProviderNodeVisitor($type);
    }

    public function parseAstOf($file)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        return $parser->parse($file);
    }
}
