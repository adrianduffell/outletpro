<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

/**
 * Removes `require_once` statements that include files containing 'license.php' in their path.
 *
 * Targets:
 *   require_once __DIR__ . '/includes/license.php';   in outletpro.php
 */
final class RemoveLicenseRequireRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     * @return NodeVisitor::REMOVE_NODE|null
     */
    public function refactor(Node $node)
    {
        if (! $node->expr instanceof Include_) {
            return null;
        }

        if ($this->pathContainsLicenseFile($node->expr->expr)) {
            return NodeVisitor::REMOVE_NODE;
        }

        return null;
    }

    /**
     * Recursively checks whether any String_ leaf in the path expression
     * contains 'license.php'.
     */
    private function pathContainsLicenseFile(Node $expr): bool
    {
        if ($expr instanceof String_) {
            return str_contains($expr->value, 'license.php');
        }

        if ($expr instanceof Concat) {
            return $this->pathContainsLicenseFile($expr->left)
                || $this->pathContainsLicenseFile($expr->right);
        }

        return false;
    }
}
