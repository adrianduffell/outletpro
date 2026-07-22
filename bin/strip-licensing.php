<?php

/**
 * Rector configuration for stripping all licensing and welcome-page machinery
 * from the Outlet Pro plugin, producing a clean build for the Woo Marketplace.
 *
 * Usage:
 *   composer run strip-licensing
 *
 * This applies five custom rules across PHP source files, then the companion
 * shell script (bin/strip-licensing-cleanup.sh) deletes the remaining files
 * and removes the welcome-page TypeScript import.
 *
 * Requires PHP 8.2+ and rector/rector ^2.0 installed as a dev dependency.
 * Install with: composer install (--ignore-platform-reqs if your platform pin is < 8.2)
 */

declare(strict_types=1);

// Load each custom rule so Rector's DI container can find the class.
require_once __DIR__ . '/rector-rules/RemoveLicenseConstantsRector.php';
require_once __DIR__ . '/rector-rules/RemoveLicenseFunctionsRector.php';
require_once __DIR__ . '/rector-rules/RemoveLicenseCallsRector.php';
require_once __DIR__ . '/rector-rules/RemoveLicenseRequireRector.php';
require_once __DIR__ . '/rector-rules/RemoveLicenseTestMethodsRector.php';

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../outletpro.php',
        __DIR__ . '/../includes',
        __DIR__ . '/../tests',
    ])
    ->withRules([
        RemoveLicenseConstantsRector::class,
        RemoveLicenseFunctionsRector::class,
        RemoveLicenseCallsRector::class,
        RemoveLicenseRequireRector::class,
        RemoveLicenseTestMethodsRector::class,
    ]);
