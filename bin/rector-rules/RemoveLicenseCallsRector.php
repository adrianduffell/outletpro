<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

/**
 * Removes licensing-related function call statements, covering:
 *
 * 1. Direct calls to licensing functions:
 *    - init_license()         in outletpro.php admin_init_hook
 *    - init_admin_menu()      in outletpro.php init_hook (inside if-is_admin block)
 *    - register_license_key_setting() in settings.php init_settings
 *
 * 2. add_action / remove_action / add_filter / remove_filter calls whose callback
 *    string matches a licensing function:
 *    - 'OutletPro\enqueue_admin_welcome_page_scripts_hook'  in enqueue.php
 *    - 'OutletPro\add_license_menu_hook'                    in admin-menu.php (inside removed fns)
 *    - 'OutletPro\add_welcome_menu_hook'                    in admin-menu.php (inside removed fns)
 *    - 'OutletPro\add_plugin_action_links_hook'             in license.php (inside removed fns)
 *
 * 3. wp_dequeue_script / wp_deregister_script with the welcome-page handle:
 *    - wp_dequeue_script( 'outletpro-welcome-page' )        in enqueue.php deinit_enqueue
 *    - wp_deregister_script( 'outletpro-welcome-page' )     in enqueue.php deinit_enqueue
 *
 * 4. If_ blocks whose stmts are exclusively direct licensing function calls
 *    (handles the `if ( is_admin() ) { init_admin_menu(); }` block in outletpro.php).
 */
final class RemoveLicenseCallsRector extends AbstractRector
{
    /**
     * Unqualified function names that should be removed when called directly.
     */
    private const DIRECT_CALLS_TO_REMOVE = [
        'init_license',
        'init_admin_menu',
        'register_license_key_setting',
    ];

    /**
     * Fully-qualified callback strings (namespace + backslash + function name) used in
     * add_action / remove_action / add_filter / remove_filter calls that must be removed.
     * Single-quoted here so the backslash is a literal backslash, matching what PHP-Parser
     * stores from source strings like 'OutletPro\fn_name'.
     */
    private const LICENSING_CALLBACKS = [
        'OutletPro\add_plugin_action_links_hook',
        'OutletPro\add_license_menu_hook',
        'OutletPro\add_welcome_menu_hook',
        'OutletPro\enqueue_admin_welcome_page_scripts_hook',
    ];

    private const HOOK_FUNCTIONS = ['add_action', 'remove_action', 'add_filter', 'remove_filter'];

    private const WELCOME_SCRIPT_HANDLE = 'outletpro-welcome-page';

    private const SCRIPT_HANDLE_FUNCTIONS = ['wp_dequeue_script', 'wp_deregister_script'];

    public function getNodeTypes(): array
    {
        return [Expression::class, If_::class];
    }

    /**
     * @param Expression|If_ $node
     * @return NodeVisitor::REMOVE_NODE|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Expression) {
            return $this->refactorExpression($node);
        }

        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }

        return null;
    }

    /**
     * @return NodeVisitor::REMOVE_NODE|null
     */
    private function refactorExpression(Expression $node)
    {
        if (! $node->expr instanceof FuncCall) {
            return null;
        }

        $funcCall = $node->expr;
        $funcName = $this->getFuncName($funcCall);

        if ($funcName === null) {
            return null;
        }

        // 1. Direct licensing function calls.
        if (in_array($funcName, self::DIRECT_CALLS_TO_REMOVE, true)) {
            return NodeVisitor::REMOVE_NODE;
        }

        // 2. Hook registration / de-registration with a licensing callback.
        if (in_array($funcName, self::HOOK_FUNCTIONS, true)) {
            $callbackArg = $this->getPositionalArg($funcCall, 1);
            if ($callbackArg instanceof Arg && $callbackArg->value instanceof String_) {
                if (in_array($callbackArg->value->value, self::LICENSING_CALLBACKS, true)) {
                    return NodeVisitor::REMOVE_NODE;
                }
            }
        }

        // 3. Script de-queue / de-registration for the welcome-page handle.
        if (in_array($funcName, self::SCRIPT_HANDLE_FUNCTIONS, true)) {
            $handleArg = $this->getPositionalArg($funcCall, 0);
            if ($handleArg instanceof Arg && $handleArg->value instanceof String_) {
                if ($handleArg->value->value === self::WELCOME_SCRIPT_HANDLE) {
                    return NodeVisitor::REMOVE_NODE;
                }
            }
        }

        return null;
    }

    /**
     * Remove an If_ block when every stmt inside it is a direct licensing function call.
     * This handles `if ( is_admin() ) { init_admin_menu(); }` in outletpro.php.
     *
     * @return NodeVisitor::REMOVE_NODE|null
     */
    private function refactorIf(If_ $node)
    {
        // Do not touch blocks with else / elseif branches.
        if (! empty($node->elseifs) || $node->else !== null) {
            return null;
        }

        if (empty($node->stmts)) {
            return null;
        }

        foreach ($node->stmts as $stmt) {
            if (! $this->isDirectLicensingCall($stmt)) {
                return null;
            }
        }

        return NodeVisitor::REMOVE_NODE;
    }

    /**
     * Returns true when the statement is a bare call to one of the DIRECT_CALLS_TO_REMOVE
     * functions (e.g. `init_admin_menu();`).
     */
    private function isDirectLicensingCall(Stmt $stmt): bool
    {
        if (! $stmt instanceof Expression) {
            return false;
        }

        if (! $stmt->expr instanceof FuncCall) {
            return false;
        }

        $funcName = $this->getFuncName($stmt->expr);
        return $funcName !== null && in_array($funcName, self::DIRECT_CALLS_TO_REMOVE, true);
    }

    /**
     * Returns the unqualified function name from a FuncCall, or null for dynamic calls.
     */
    private function getFuncName(FuncCall $funcCall): ?string
    {
        if (! $funcCall->name instanceof Node\Name) {
            return null;
        }

        // getLast() returns the final part of the name (works for both unqualified and
        // fully-qualified names: `init_license` → 'init_license', `\add_action` → 'add_action').
        return $funcCall->name->getLast();
    }

    /**
     * Returns the Arg at the given positional index (ignoring named arguments),
     * or null when the index does not exist or is not a plain Arg.
     */
    private function getPositionalArg(FuncCall $funcCall, int $index): ?Arg
    {
        $positional = [];
        foreach ($funcCall->args as $arg) {
            if ($arg instanceof Arg && $arg->name === null) {
                $positional[] = $arg;
            }
        }

        return $positional[$index] ?? null;
    }
}
