<?php

namespace Beyondcode\NovaInstaller\Utils;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

class ProviderNodeVisitor extends NodeVisitorAbstract
{
    public $type;
    public $line;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            if ($node->name == $this->type) {
                foreach ($node->getStmts() as $stmt) {
                    if ($stmt->getType() == 'Stmt_Return') {
                        $this->line = $stmt->getEndLine();
                        break;
                    }
                }
            }
        }
    }
}
