# Public API

Outlet implements a stable public API intended for use by third-party code. All items listed here increment the plugin's MAJOR semver version when any known incompatible changes are made.

## Functions

### Outlet status

#### `OutletPro\is_outlet( \WC_Product $product ): bool`

Check if a product is in the store’s outlet.

Throws exception on error. Added in 1.0.0.

| Parameter  | Type          | Description           |
| ---------- | ------------- | --------------------- |
| `$product` | `\WC_Product` | The product to check. |

```php
try {
    $is_outlet = OutletPro\is_outlet( $product );
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\add_to_outlet( \WC_Product $product ): void`

Add a product to the store’s outlet.

Throws exception on error. Added in 1.0.0.

| Parameter  | Type          | Description         |
| ---------- | ------------- | ------------------- |
| `$product` | `\WC_Product` | The product to add. |

```php
try {
    OutletPro\add_to_outlet( $product );
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\remove_from_outlet( \WC_Product $product ): void`

Remove a product from the store’s outlet.

Throws exception on error. Added in 1.0.0.

| Parameter  | Type          | Description            |
| ---------- | ------------- | ---------------------- |
| `$product` | `\WC_Product` | The product to remove. |

```php
try {
    OutletPro\remove_from_outlet( $product );
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\set_outlet( \WC_Product $product, bool $new_value ): void`

Set the outlet status for a product.

Fires the `wc_outlet_status_changed` action on a status change.

Throws exception on error. Added in 1.0.0.

| Parameter    | Type          | Description                                 |
| ------------ | ------------- | ------------------------------------------- |
| `$product`   | `\WC_Product` | The product to update.                      |
| `$new_value` | `bool`        | `true` to add to outlet, `false` to remove. |

```php
try {
    OutletPro\set_outlet( $product, true );
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\count_outlet(): int`

Count the number of published products in the store’s outlet.

Throws exception on error. Added in 1.0.0.

```php
try {
    $count = OutletPro\count_outlet();
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\outlet_empty(): bool`

Check if the store’s outlet has no published products.

More performant than `count_outlet()` because it skips the SQL `COUNT(*)`.

Throws exception on error. Added in 1.0.0.

```php
try {
    if ( OutletPro\outlet_empty() ) {
        // nothing to display
    }
} catch ( \Throwable $e ) {
    // Handle exception
}
```

### Outlet page

#### `OutletPro\get_outlet_page_id(): ?int`

Get the outlet page ID from the `wc_outlet_page_id` option.

Returns the page ID as a normalised `int`, or `null` when the option does not exist.

Throws exception on error. Added in 1.0.0.

```php
try {
    $page_id = OutletPro\get_outlet_page_id();
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\outlet_page_exists(): bool`

Check if the outlet page exists.

Uses heuristics on the `wc_outlet_page_id` option value. Returns `false` when the option
is missing. Trashed pages are not considered to exist.

Throws exception on error. Added in 1.0.0.

```php
try {
    if ( OutletPro\outlet_page_exists() ) {
        // page is present
    }
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\outlet_page_is_published(): bool`

Check whether the outlet page exists and is published.

Throws exception on error. Added in 1.0.0.

```php
try {
    if ( OutletPro\outlet_page_is_published() ) {
        // page is live
    }
} catch ( \Throwable $e ) {
    // Handle exception
}
```

#### `OutletPro\create_outlet_page(): void`

Create the outlet page.

Does nothing when a outlet page is already registered, preventing duplicates.
The created page is a draft. The page content varies depending on whether the active
theme is a block theme or a classic theme.

Throws exception on error. Added in 1.0.0.

```php
try {
    OutletPro\create_outlet_page();
} catch ( \Throwable $e ) {
    // Handle exception
}
```

## Hooks

### Actions

#### `wc_outlet_status_changed`

Fires when a product's outlet status changes.

```php
add_action( 'wc_outlet_status_changed', function ( $product_id, $old_value, $new_value ) {
    // React to the outlet status change.
}, 10, 3 );
```

| Parameter     | Type   | Description             |
| ------------- | ------ | ----------------------- |
| `$product_id` | `int`  | Product ID.             |
| `$old_value`  | `bool` | Previous outlet status. |
| `$new_value`  | `bool` | New outlet status.      |

Added in 1.0.0.

### Filters

#### `wc_outlet_badge_single_product_hook`

Filter to modify which `single-product` [WooCommerce template hook](https://developer.woocommerce.com/docs/theming/theme-development/template-structure/#changing-templates-via-hooks) (or theme hook) to display the outlet badge on.

```php
add_filter( 'wc_outlet_badge_single_product_hook', function ( $name ) {
    return 'woocommerce_before_single_product';
} );
```

| Parameter | Type     | Description                                              |
| --------- | -------- | -------------------------------------------------------- |
| `$name`   | `string` | Hook name. Default `woocommerce_single_product_summary`. |

Must return a non-empty string. Added in 1.0.0.

#### `wc_outlet_badge_single_product_priority`

Filters the priority used for [hooking](https://developer.woocommerce.com/docs/theming/theme-development/template-structure/#changing-templates-via-hooks) the outlet badge to the `single-product` classic templates.

```php
add_filter( 'wc_outlet_badge_single_product_priority', function ( $priority ) {
    return 5;
} );
```

| Parameter   | Type  | Description                  |
| ----------- | ----- | ---------------------------- |
| `$priority` | `int` | Hook priority. Default `15`. |

Must return an integer. Added in 1.0.0.

## Script and style handles

These handles are registered by the plugin and can be used as dependencies in third-party
enqueues.

### Styles

#### `wc-outlet`

Front-end stylesheet for classic (non-block) themes. Registered — but not automatically
enqueued — on `wp_enqueue_scripts`. Use `wp_enqueue_style( 'wc-outlet' )` or declare
it as a dependency to load it on demand. Added in 1.0.0.

#### `wc-outlet-block-styles`

Stylesheet for the outlet badge block. Registered via `wp_enqueue_block_style` so it
is only loaded when the `outlet-pro/outlet-badge` block is rendered on the page.
Added in 1.0.0.

#### `wc-outlet-admin-styles`

Admin stylesheet enqueued on all `admin_enqueue_scripts` pages. Added in 1.0.0.

### Scripts

#### `wc-outlet-build`

Block editor JavaScript enqueued on `enqueue_block_editor_assets`. Contains the block
editor integration for the outlet badge and outlet message blocks. Added in 1.0.0.

#### `wc-outlet-admin-product`

Admin JavaScript enqueued on `admin_enqueue_scripts` for the product edit screen only.
Added in 1.0.0.

## CSS classes

These classes are part of the public API and stable across versions. They can be targeted
for custom styling.

### Front-end classes

#### `.wc-outlet-badge`

Applied to the outlet badge element. Used by both the block renderer and classic theme
template hooks. Added in 1.0.0.

#### `.wc-outlet-message`

Applied to the outlet message element. Used by both the block renderer and classic theme
template hooks. Added in 1.0.0.

## Blocks

### `wc-outlet/outlet-badge`

Displays a outlet badge when the product is in the store’s outlet. Automatically
inserted after the product price on the single product template (block themes). Added
in 1.0.0.

Styles are inherited from site-wide settings. Default style values:

| Property           | Default          |
| ------------------ | ---------------- |
| `width`            | `fit-content`    |
| `text-box-trim`    | `trim-both`      |
| `text-box-edge`    | `cap alphabetic` |
| `line-height`      | `1`              |
| `background-color` | `#FFEE85`\*      |
| `color`            | `#222`\*         |
| `padding`          | Calculated\*¹    |
| `font-size`        | Calculated\*¹    |
| `font-weight`      | `600`\*          |
| `border-radius`    | `2px`\*          |

1. The default height of the badge is 1.66x the capital letter height of surrounding text.

-   The default padding on each side is 25% the height of the badge.
-   The default font-size is 50% the height of the badge.

Use the scale setting to control the height of the badge, and density (called "font-size" in the UI) to control the font-size/padding ratio.

\* Denotes modifiable in settings.

### `wc-outlet/outlet-message`

Displays the outlet message when the product is in the store’s outlet. Automatically
inserted as the first child of the product meta block on the single product template (block
themes). Added in 1.0.0.

| Attribute  | Type     | Default | Description                                |
| ---------- | -------- | ------- | ------------------------------------------ |
| `fontSize` | `string` | `small` | Text size preset (e.g. `small`, `medium`). |

## REST API

The plugin extends the WooCommerce products REST endpoint with a `wc_outlet` query
parameter.

```http
GET /wc/v3/products?wc_outlet=true
```

The plugin also extends the WordPress products REST endpoint (post type) with the same
parameter.

```http
GET /wp/v2/products?wc_outlet=true
```

| Parameter   | Type      | Description                                                    |
| ----------- | --------- | -------------------------------------------------------------- |
| `wc_outlet` | `boolean` | When `true`, limits results to products in the store's outlet. |

Added in 1.0.0.

## Shortcodes

The plugin extends the WooCommerce `[products]` shortcode with a `wc_outlet` attribute.

```text
[products wc_outlet="true"]
```

| Attribute   | Type                       | Description                                                    |
| ----------- | -------------------------- | -------------------------------------------------------------- |
| `wc_outlet` | `boolean` (`true`/`false`) | When `true`, limits results to products in the store’s outlet. |

Added in 1.0.0.

## Non-Public API

The following items are intentionally excluded from the public API. They may change at any
time without a MAJOR version bump. Do not rely on them in third-party code.

-   All code items tagged with `@internal` comment.

    These are intended only for use internally and are likely to change in refactors.

-   File paths.

    File paths are subject to change in future versions.

-   The taxonomy slug `outletpro_status` and term slug `outlet`.

    The outlet status is powered by a non-public taxonomy for performance but is considered experimental and may change in the future. Instead, use the outlet status functions or REST API parameter for stable access to the outlet status.

-   Admin dashboard CSS.

    Admin dashboard-related selectors and HTML structures are subject to change.
