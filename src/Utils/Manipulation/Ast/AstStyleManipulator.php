<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation\Ast;

use ReflectionClass;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Beyondcode\NovaInstaller\Utils\Manipulation\Manipulator;

class AstStyleManipulator implements Manipulator
{
    public $reflector;
    protected $fileContents;
    public $modifiedFileContents;
    protected $ast;
    protected $visitor;

    public function reflect($classname)
    {
        $this->reflector = new ReflectionClass($classname);
    }

    public function readFile()
    {
        $this->fileContents = file_get_contents($this->reflector->getFileName());
        $this->parseAst();
    }

    public function writeFile()
    {
        file_put_contents($this->reflector->getFileName(), join('', $this->modifiedFileContents));
    }

    public function installProviderOfType($provider, $type)
    {
        $this->makeNodeVisitorLookingForType($type);

        $this->traverseAstWithVisitor();

        $fileAsArray  = file($this->reflector->getFileName());

        array_splice($fileAsArray, $this->visitor->line - 1, 0, str_repeat(" ", 12) . $provider . ",\n");

        $this->modifiedFileContents = $fileAsArray;
    }

    public function isInstalled($classname)
    {
        return strpos($this->fileContents, $classname);
    }

    protected function traverseAstWithVisitor()
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor($this->visitor);

        $traverser->traverse($this->ast);
    }

    protected function makeNodeVisitorLookingForType($type)
    {
        $this->visitor = new ProviderNodeVisitor($type);
    }

    public function parseAst()
    {
        $this->ast = (new ParserFactory)->create(ParserFactory::PREFER_PHP7)->parse($this->fileContents);
    }
}
