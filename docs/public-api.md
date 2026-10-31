# Public API

Outlet implements a stable public API intended for use by third-party code. All
items listed here increment the plugin's MAJOR semver version when any known
incompatible changes are made.

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

Fires the `outletpro_status_changed` action on a status change.

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

### Price and discount tiers

Manage the available tiers using numeric amounts, independently of how tiers are
stored. Added in 1.1.0.

- `OutletPro\add_price_tier( int $price ): void`
- `OutletPro\get_price_tiers(): array`
- `OutletPro\update_price_tier( int $price, int $new_price ): void`
- `OutletPro\delete_price_tier( int $price ): void`
- `OutletPro\add_discount_tier( int $discount ): void`
- `OutletPro\get_discount_tiers(): array`
- `OutletPro\update_discount_tier( int $discount, int $new_discount ): void`
- `OutletPro\delete_discount_tier( int $discount ): void`

Prices are non-negative integer thresholds in the store's currency. Discounts
are integer percentages from 0 to 100 inclusive. The read functions return all
configured amounts in numerical order, including tiers without products.

Updates replace the existing amount with the new amount and preserve product
assignments. Deletion removes the tier globally, including its product
assignments. Adding, updating, or deleting a tier schedules outlet products for
reindexing through Action Scheduler, so their tier assignments are recalculated.

The mutation functions throw `\InvalidArgumentException` for amounts outside the
supported range. All functions throw `\RuntimeException` on storage errors.
Adding an existing tier, updating a tier to another tier's amount, or updating or
deleting a missing tier also throws `\RuntimeException`.

#### Batch operations

`OutletPro\batch_price_tiers()` and `OutletPro\batch_discount_tiers()` accept
three optional array parameters, in order: `$add`, `$update`, and `$delete`.
All default to an empty array. Added in 1.1.0.

```php
OutletPro\batch_price_tiers(
    array( 125, 150 ), // Add these amounts.
    array( 50 => 60 ), // Map existing amounts to new amounts.
    array( 100 )      // Delete these amounts.
);
```

Batches validate every amount and operation before writing. Additions run first,
then updates in array order, then deletions. Duplicate additions, missing tiers,
and updates to occupied amounts throw `\RuntimeException`. Invalid amounts throw
`\InvalidArgumentException`. Empty batches do nothing.

Each batch temporarily pauses the registered product-reindex callbacks, restores
their priorities in `finally`, and requests one reindex pass after changes.
Existing pauses and nested batches are respected. Other taxonomy callbacks still
run. A storage failure can leave earlier operations saved; those changes still
request reindexing before the exception propagates. Batches do not roll back.

#### Individual operations

```php
try {
    OutletPro\add_price_tier( 125 );
    OutletPro\update_price_tier( 125, 150 );
    $prices = OutletPro\get_price_tiers();
    OutletPro\delete_price_tier( 150 );

    OutletPro\add_discount_tier( 40 );
    OutletPro\update_discount_tier( 40, 45 );
    $discounts = OutletPro\get_discount_tiers();
    OutletPro\delete_discount_tier( 45 );
} catch ( \Throwable $e ) {
    // Handle exception
}
```

### Outlet page

#### `OutletPro\get_outlet_page_id(): ?int`

Get the outlet page ID from the `outletpro_page_id` option.

Returns the page ID as a normalised `int`, or `null` when the option does not
exist.

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

Uses heuristics on the `outletpro_page_id` option value. Returns `false` when
the option is missing. Trashed pages are not considered to exist.

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
The created page is a draft. The page content varies depending on whether the
active theme is a block theme or a classic theme.

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

#### `outletpro_status_changed`

Fires when a product's outlet status changes.

```php
add_action(
    'outletpro_status_changed',
    function ( $product_id, $old_value, $new_value ) {
        // React to the outlet status change.
    },
    10,
    3
);
```

| Parameter     | Type   | Description             |
| ------------- | ------ | ----------------------- |
| `$product_id` | `int`  | Product ID.             |
| `$old_value`  | `bool` | Previous outlet status. |
| `$new_value`  | `bool` | New outlet status.      |

Added in 1.0.0.

### Filters

#### `outletpro_badge_single_product_hook`

Filter to modify which `single-product`
[WooCommerce template hook][template-hooks] (or theme hook) to display the
outlet badge on.

```php
add_filter( 'outletpro_badge_single_product_hook', function ( $name ) {
    return 'woocommerce_before_single_product';
} );
```

| Parameter | Type     | Default                              | Description |
| --------- | -------- | ------------------------------------ | ----------- |
| `$name`   | `string` | `woocommerce_single_product_summary` | Hook name.  |

Must return a non-empty string. Added in 1.0.0.

#### `outletpro_badge_single_product_priority`

Filters the priority used for [hooking][template-hooks] the outlet badge to the
`single-product` classic templates.

```php
add_filter( 'outletpro_badge_single_product_priority', function ( $priority ) {
    return 5;
} );
```

| Parameter   | Type  | Default | Description   |
| ----------- | ----- | ------- | ------------- |
| `$priority` | `int` | `1`     | Hook priority |

Must return an integer. Added in 1.0.0.

## Script and style handles

These handles are registered by the plugin and can be used as dependencies in
third-party enqueues.

### Styles

| Handle                      | Description                       | Added |
| --------------------------- | --------------------------------- | ----- |
| `outletpro-classic-badge`   | Outlet badge for classic themes   | 1.0.0 |
| `outletpro-classic-message` | Outlet message for classic themes | 1.0.0 |
| `outletpro-block-cart`      | Outlet badge in block-based carts | 1.1.0 |

### Scripts

| Handle                 | Description                              | Added |
| ---------------------- | ---------------------------------------- | ----- |
| `outletpro-block-cart` | Inserts the badge into block-based carts | 1.1.0 |

## CSS classes

These classes are part of the public API and stable across versions. They can be
targeted for custom styling.

### Front-end classes

#### `.outletpro-badge`

Applied to the outlet badge element. Used by both the block renderer and classic
theme template hooks. Added in 1.0.0.

#### `.outletpro-message`

Applied to the outlet message element. Used by both the block renderer and
classic theme template hooks. Added in 1.0.0.

## Blocks

### `outletpro/outlet-badge`

Displays a outlet badge when the product is in the store’s outlet. Automatically
inserted before the product name on the single product template (block themes).
Added in 1.0.0.

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

1. The default height of the badge is 1.66x the capital letter height of
   surrounding text.

- The default padding on each side is 25% the height of the badge.
- The default font-size is 50% the height of the badge.

Use the scale setting to control the height of the badge, and density (called
"font-size" in the UI) to control the font-size/padding ratio.

\* Denotes modifiable in settings.

### `outletpro/outlet-conditional`

A container for arbitrary blocks that renders only for outlet products. Added in
1.1.0.

### `outletpro/outlet-message`

Displays the outlet message when the product is in the store’s outlet.
Automatically inserted as the first child of the product meta block on the
single product template (block themes). Added in 1.0.0.

| Attribute  | Type     | Default | Description      |
| ---------- | -------- | ------- | ---------------- |
| `fontSize` | `string` | `small` | Font size preset |

## REST API

### WooCommerce Store API cart items

Each item returned by the WooCommerce Store API cart endpoints includes its
outlet status in the Outlet Pro extension namespace.

```json
{
  "extensions": {
    "outletpro": {
      "is_outlet": true
    }
  }
}
```

Added in 1.1.0.

### Product filtering

The plugin extends the WooCommerce products REST endpoint with a `outletpro`
query parameter.

```http
GET /wc/v3/products?outletpro=true
```

The plugin also extends the WordPress products REST endpoint (post type) with
the same parameter.

```http
GET /wp/v2/products?outletpro=true
```

| Parameter   | Type      | Description                               |
| ----------- | --------- | ----------------------------------------- |
| `outletpro` | `boolean` | When `true`, returns outlet products only |

Added in 1.0.0.

## Shortcodes

The plugin extends the WooCommerce `[products]` shortcode with a `outletpro`
attribute.

```text
[products outletpro="true"]
```

| Attribute   | Type      | Description                               |
| ----------- | --------- | ----------------------------------------- |
| `outletpro` | `boolean` | When `true`, returns outlet products only |

Added in 1.0.0.

## Non-Public API

The following items are intentionally excluded from the public API. They may
change at any time without a MAJOR version bump. Do not rely on them in
third-party code.

- All code items tagged with `@internal` comment.

  These are intended only for use internally and are likely to change in
  refactors.

- File paths.

  File paths are subject to change in future versions.

- The taxonomy slug `outletpro_status` and term slug `outlet`.

  The outlet status is powered by a non-public taxonomy for performance but is
  considered experimental and may change in the future. Instead, use the outlet
  status functions or REST API parameter for stable access to the outlet status.

- Admin dashboard CSS.

  Admin dashboard-related selectors and HTML structures are subject to change.

[template-hooks]: https://developer.woocommerce.com/docs/theming/theme-development/template-structure/#changing-templates-via-hooks
