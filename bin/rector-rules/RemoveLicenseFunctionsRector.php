<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

/**
 * Removes licensing-related PHP function declarations:
 *
 * From includes/license.php (file is deleted entirely by cleanup script):
 * - init_license, deinit_license, validate_license, has_license
 * - add_plugin_action_links_hook, render_license_page
 *
 * From includes/admin-menu.php:
 * - init_admin_menu, deinit_admin_menu
 * - add_license_menu_hook, add_welcome_menu_hook, render_welcome_page
 *
 * From includes/enqueue.php:
 * - enqueue_admin_welcome_page_scripts_hook
 */
final class RemoveLicenseFunctionsRector extends AbstractRector
{
    private const FUNCTIONS_TO_REMOVE = [
        // license.php
        'init_license',
        'deinit_license',
        'validate_license',
        'has_license',
        'add_plugin_action_links_hook',
        'render_license_page',
        // admin-menu.php
        'init_admin_menu',
        'deinit_admin_menu',
        'add_license_menu_hook',
        'add_welcome_menu_hook',
        'render_welcome_page',
        // enqueue.php
        'enqueue_admin_welcome_page_scripts_hook',
    ];

    public function getNodeTypes(): array
    {
        return [Function_::class];
    }

    /**
     * @param Function_ $node
     * @return NodeVisitor::REMOVE_NODE|null
     */
    public function refactor(Node $node)
    {
        if (in_array((string) $node->name, self::FUNCTIONS_TO_REMOVE, true)) {
            return NodeVisitor::REMOVE_NODE;
        }

        return null;
    }
}
