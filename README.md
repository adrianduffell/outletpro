# Outlet Pro

Move old stock easily by adding a dedicated outlet to WooCommerce.

## Overview

Outlet Pro adds outlet functionality to WooCommerce stores, including:

- A dedicated outlet page.
- Product-level outlet assignment (single edit and bulk edit).
- Configurable outlet badge and message.
- Outlet filtering support for REST API and WooCommerce shortcodes.

For WordPress.org plugin metadata, see `readme.txt`.

## Requirements

- WordPress 6.9+
- WooCommerce
- PHP 7.4+
- Node.js 24.18.0 (for JS tooling)

## Development setup

1. Install dependencies:
   - `npm install`
   - `composer install`
2. Start local WordPress environment:
   - `npm run wp-env start`
3. Build plugin assets:
   - `npm run build`

The local environment is configured via `.wp-env.json` and `.wp-env.e2e.json`.

## Scripts

### Build and development

- `npm run build` – Build production JS assets.
- `npm run start` – Start JS development build/watch.
- `npm run plugin-zip` – Build distributable plugin ZIP.
- `npm run preprocess` – Run preprocessing script.
- `npm run preprocess:marketplace` – Strip marketplace-incompatible code paths.

### Linting and formatting

- `npm run format`
- `npm run lint:css`
- `npm run lint:css:fix`
- `npm run lint:js`
- `npm run lint:js:fix`
- `npm run lint:md-docs`
- `npm run lint:md-docs:fix`
- `npm run lint:php`
- `npm run lint:php:fix`

### Tests and checks

- `npm run test:unit` – JavaScript unit tests.
- `npm run test:wp` – PHPUnit tests in wp-env.
- `npm run test:e2e` – Playwright E2E tests.
- `npm run test:e2e:block` – E2E tests against a block-theme setup.
- `npm run test:e2e:classic` – E2E tests against a classic-theme setup.
- `npm run typecheck` – TypeScript type checks.

## Additional docs

- Public API: `docs/public-api.md`
- Terminology glossary: `docs/glossary.md`
