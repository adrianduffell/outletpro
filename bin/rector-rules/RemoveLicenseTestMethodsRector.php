<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

/**
 * Removes test methods in tests/test-deinit-enqueue.php that test welcome-page
 * script behaviour which is stripped in the Woo Marketplace build.
 *
 * Only acts when the current file path contains 'test-deinit-enqueue' to avoid
 * accidentally removing identically-named methods in other test classes.
 *
 * Methods removed:
 *   - test_removes_admin_enqueue_scripts_welcome_page_scripts_hook
 *   - test_deregisters_welcome_page_script
 *   - test_safely_handles_welcome_page_script_not_registered
 */
final class RemoveLicenseTestMethodsRector extends AbstractRector
{
    private const METHODS_TO_REMOVE = [
        'test_removes_admin_enqueue_scripts_welcome_page_scripts_hook',
        'test_deregisters_welcome_page_script',
        'test_safely_handles_welcome_page_script_not_registered',
    ];

    private const TARGET_FILE_FRAGMENT = 'test-deinit-enqueue';

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     * @return NodeVisitor::REMOVE_NODE|null
     */
    public function refactor(Node $node)
    {
        $filePath = $this->file->getFilePath();

        if (! str_contains($filePath, self::TARGET_FILE_FRAGMENT)) {
            return null;
        }

        if (in_array((string) $node->name, self::METHODS_TO_REMOVE, true)) {
            return NodeVisitor::REMOVE_NODE;
        }

        return null;
    }
}
