# Public API

Clearance Section implements [semver versioning](https://semver.org) and includes a public API of stable integration points intended for use by third-party code. All items listed here increment the plugin's MAJOR version when any known incompatible changes are made.

## Functions

### Clearance status

#### `WC_Clearance\is_clearance( WC_Product $product ): bool`

Check if a product is in the clearance section.

Throws `\RuntimeException` if the clearance status taxonomy does not exist.
Added in 1.0.0.

#### `WC_Clearance\add_to_clearance( WC_Product $product ): void`

Add a product to the clearance section.

Throws `\RuntimeException` if the clearance status taxonomy does not exist or the term
assignment fails. Added in 1.0.0.

#### `WC_Clearance\remove_from_clearance( WC_Product $product ): void`

Remove a product from the clearance section.

Throws `\RuntimeException` if the clearance status taxonomy does not exist or term
removal fails. Added in 1.0.0.

#### `WC_Clearance\set_clearance( WC_Product $product, bool $new_value ): void`

Set the clearance section status for a product.

Checks the current stored state and only calls `add_to_clearance()` or
`remove_from_clearance()` when a change is required. Fires the
`wc_clearance_status_changed` action on a status change.

Throws `\RuntimeException` if setting the status fails. Added in 1.0.0.

#### `WC_Clearance\count_clearance(): int`

Count the number of published products in the clearance section.

Throws `\RuntimeException` if the clearance status taxonomy does not exist.
Added in 1.0.0.

#### `WC_Clearance\clearance_section_empty(): bool`

Check if the clearance section has no published products.

More performant than `count_clearance()` because it skips the SQL `COUNT(*)`.

Throws `\RuntimeException` if the clearance status taxonomy does not exist.
Added in 1.0.0.

### Clearance page

#### `WC_Clearance\get_clearance_page_id(): ?int`

Get the clearance section page ID from the `wc_clearance_page_id` option.

Returns the page ID as a normalised `int`, or `null` when the option does not exist.

Throws `\UnexpectedValueException` if the stored value is not a positive integer.
Added in 1.0.0.

#### `WC_Clearance\clearance_page_exists(): bool`

Check if the clearance section page exists.

Uses heuristics on the `wc_clearance_page_id` option value. Returns `false` when the option
is missing. Trashed pages are not considered to exist.

Throws `\UnexpectedValueException` if the stored option value is not a positive integer.
Added in 1.0.0.

#### `WC_Clearance\clearance_page_is_published(): bool`

Check whether the clearance section page exists and is published.

Throws `\RuntimeException` if existence cannot be determined. Added in 1.0.0.

#### `WC_Clearance\create_clearance_page(): void`

Create the clearance section page.

Does nothing when a clearance page is already registered, preventing duplicates.
The created page is a draft. The page content varies depending on whether the active
theme is a block theme or a classic theme.

Throws `\RuntimeException` if existence cannot be determined, or if page creation fails.
Added in 1.0.0.

## Hooks

### Actions

#### `wc_clearance_status_changed`

Fires when a product's clearance section status changes.

```php
do_action( 'wc_clearance_status_changed', int $product_id, bool $old_value, bool $new_value );
```

| Parameter | Type | Description |
|---|---|---|
| `$product_id` | `int` | Product ID. |
| `$old_value` | `bool` | Previous clearance status. |
| `$new_value` | `bool` | New clearance status. |

Added in 1.0.0.

### Filters

#### `wc_clearance_badge_single_product_hook`

Filter to modify which `single-product` [WooCommerce template hook](https://developer.woocommerce.com/docs/theming/theme-development/template-structure/#changing-templates-via-hooks) (or theme hook) to display the clearance badge on.

```php
apply_filters( 'wc_clearance_badge_single_product_hook', string $name )
```

| Parameter | Type | Description |
|---|---|---|
| `$name` | `string` | Hook name. Default `woocommerce_single_product_summary`. |

Must return a non-empty string. Added in 1.0.0.

#### `wc_clearance_badge_single_product_priority`

Filters the priority used for [hooking](https://developer.woocommerce.com/docs/theming/theme-development/template-structure/#changing-templates-via-hooks) the clearance badge to the `single-product` classic templates.

```php
apply_filters( 'wc_clearance_badge_single_product_priority', int $priority )
```

| Parameter | Type | Description |
|---|---|---|
| `$priority` | `int` | Hook priority. Default `15`. |

Must return an integer. Added in 1.0.0.

## Script and style handles

These handles are registered by the plugin and can be used as dependencies in third-party
enqueues.

### Styles

#### `wc-clearance`

Front-end stylesheet for classic (non-block) themes. Registered — but not automatically
enqueued — on `wp_enqueue_scripts`. Use `wp_enqueue_style( 'wc-clearance' )` or declare
it as a dependency to load it on demand. Added in 1.0.0.

#### `wc-clearance-block-styles`

Stylesheet for the clearance badge block. Registered via `wp_enqueue_block_style` so it
is only loaded when the `wc-clearance/clearance-badge` block is rendered on the page.
Added in 1.0.0.

#### `wc-clearance-admin-styles`

Admin stylesheet enqueued on all `admin_enqueue_scripts` pages. Added in 1.0.0.

### Scripts

#### `wc-clearance-build`

Block editor JavaScript enqueued on `enqueue_block_editor_assets`. Contains the block
editor integration for the clearance badge and clearance message blocks. Added in 1.0.0.

#### `wc-clearance-admin-product`

Admin JavaScript enqueued on `admin_enqueue_scripts` for the product edit screen only.
Added in 1.0.0.

## Blocks

### `wc-clearance/clearance-badge`

Displays a clearance badge when the product is in the clearance section. Automatically
inserted after the product price on the single product template (block themes). Added
in 1.0.0.

#### Attributes

| Attribute | Type | Description |
|---|---|---|
| `style` | `object` | Standard block style object. Controls color (background, text, gradients), spacing (padding, margin), typography (fontSize, fontWeight), and border (radius, width, color). |

Default style values:

| Property | Default |
|---|---|
| `style.color.background` | `#FFEE85` |
| `style.color.text` | `#222` |
| `style.spacing.padding` | `5px` on all sides |
| `style.typography.fontSize` | `0.875rem` |
| `style.typography.fontWeight` | `600` |
| `style.border.radius` | `2px` |

### `wc-clearance/clearance-message`

Displays the clearance message when the product is in the clearance section. Automatically
inserted as the first child of the product meta block on the single product template (block
themes). Added in 1.0.0.

#### clearance-message attributes

| Attribute | Type | Default | Description |
|---|---|---|---|
| `fontSize` | `string` | `small` | Text size preset (e.g. `small`, `medium`). |

### `wc-clearance/product-collection/clearance`

A product collection variation that shows only clearance products. Available in the block
editor when inserting a Product Collection block. Added in 1.0.0.

## REST API

The plugin extends the WooCommerce products REST endpoint with a `wc_clearance` query
parameter.

```http
GET /wc/v3/products?wc_clearance=true
```

| Parameter | Type | Description |
|---|---|---|
| `wc_clearance` | `boolean` | When `true`, limits results to products in the clearance section. |

Added in 1.0.0.

## Shortcodes

The plugin extends the WooCommerce `[products]` shortcode with a `wc_clearance` attribute.

```text
[products wc_clearance="true"]
```

| Attribute | Type | Description |
|---|---|---|
| `wc_clearance` | `boolean` (`true`/`false`) | When `true`, limits results to products in the clearance section. |

Added in 1.0.0.

## CSS classes

These classes are part of the public API and stable across versions. They can be targeted
for custom styling.

### Front-end classes

#### `.wc-clearance-badge`

Applied to the clearance badge element. Used by both the block renderer and classic theme
template hooks. Added in 1.0.0.

#### `.wc-clearance-badge-container`

Wraps the badge in classic (non-block) themes. Added in 1.0.0.

#### `.wc-clearance-message`

Applied to the clearance message element. Used by both the block renderer and classic theme
template hooks. Added in 1.0.0.

## Non-Public API

The following items are intentionally excluded from the public API. They may change at any
time without a MAJOR version bump. Do not rely on them in third-party code.

* All code items tagged with `@internal` comment.

  These are intended only for use internally and are likely to change in refactors.

* File paths.

  File paths are subject to change in future versions.

* The taxonomy slug `wc_clearance_status` and term slug `clearance`.

  The clearance status is powered by a non-public taxonomy for performance but is considered experimental and may change in the future. Instead, use the clearance status functions or REST API parameter for stable access to the clearance status.

* Admin dashboard CSS.

  Admin dashboard-related selectors and HTML structures are subject to change.
