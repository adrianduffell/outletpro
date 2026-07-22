<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Stmt\Const_;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

/**
 * Removes licensing-related PHP constants:
 * - LICENSE_KEY_OPTION
 * - HAS_LICENSE_TRANSIENT
 * - MIN_LICENSE_KEY_LENGTH
 * - LICENSE_PAGE_SLUG
 * - WELCOME_PAGE_SLUG
 */
final class RemoveLicenseConstantsRector extends AbstractRector
{
    private const CONSTANTS_TO_REMOVE = [
        'LICENSE_KEY_OPTION',
        'HAS_LICENSE_TRANSIENT',
        'MIN_LICENSE_KEY_LENGTH',
        'LICENSE_PAGE_SLUG',
        'WELCOME_PAGE_SLUG',
    ];

    public function getNodeTypes(): array
    {
        return [Const_::class];
    }

    /**
     * @param Const_ $node
     * @return NodeVisitor::REMOVE_NODE|null
     */
    public function refactor(Node $node)
    {
        foreach ($node->consts as $const) {
            if (in_array((string) $const->name, self::CONSTANTS_TO_REMOVE, true)) {
                return NodeVisitor::REMOVE_NODE;
            }
        }

        return null;
    }
}
