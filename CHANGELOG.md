# Changelog

© 2026 Adrian Duffell

## 1.1.0 (Unreleased)

Adds modern category views for the Outlet Page, including a featured category
tiles and a full menu. The tiles and menu may link to filtered views of the
outlet by:

- Product categories ( eg. Womens, Mens, Kids)
- Price tiers (eg "Under $25")
- Discount tiers (eg "50% off")

The outlet badge is moved above the product name by default, as WooCommerce’s
new Purple theme places the sale badge in the previous position below the
price. Turns out placing it above the title works really well in combination
with the sale badge with the price. Outlet Pro is also updated to support
Purple fully.

The plugin is updated to support a premium subscription sold through my store,
which will enable users to start with a free trial and other goodies. A new
welcome screen is added to activate the license key for this.

Other changes:

- The cart badge now uses real HTML elements instead of CSS pseudo elements.
- A badge is shown for outlet products in the product admin list table.
- A link is added to the outlet page to customize the outlet settings.
- The project is licensed under GPL v2 or later (previously GPL v3 or later).

## 1.0.2 (2026-08-04)

Maintenance release fixing the issues:

- The field label was mis-aligned in the bulk editor.
- The outlet badge block’s label was reverting to the default value when empty.
- A PHP notice was displaying on the widget editor.
- An icon for outlet settings was shown in the site editor toolbar.

## 1.0.1 (2026-06-07)

Maintenance release adding WooCommerce declarations:

- "WC requires at least" plugin header
- "WC tested up to" plugin header
- HPOS compatibility

## 1.0.0 (2026-06-07)

Initial release includes outlet page, badge, and message. Compatible with
block and classic themes.
