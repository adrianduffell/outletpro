# Changelog

© 2026 Adrian Duffell

## 1.2.0 (2026-11-25)

Migrates the outlet page to the product catalog template for block and
classic themes. Introduces a new Outlet Hero area on the product
catalog to insert outlet content into the template on outlet pages.
This can be customized from a template part on block themes and a widget
on classic themes.

A new outlet editor is added for the outlet page, accessed from the edit
page link. Provides panel for outlet product management, and markdown and 
price levels. Additionally the badge and message can be customized from
the outlet editor.

New badge designs can now be selected: Clearance, Final sale, and Outlet.

Other changes:

- An outletpro.is_outlet field is added to the products REST API.
- Added an outlet link block that links to the outlet page.
- The outlet message is moved under the price.
- The outlet message displays a link to the outlet page.
- New styles for the outlet message: Fine, Bold, and Banner.
- An option is added to the outlet message to use a strong tag.
- The default font size is removed from the outlet message.
- The message is rendered with a p tag on classic themes.
- Padding support is added to the message block.
- Spacing on navigation patterns is standardized at preset 50.

## 1.1.1 (2026-11-07)

Maintenance release fixing the issues:

- The "uncategorized" category could appear in outlet navigation patterns.
- The outlet message was not appearing in Purple theme.
- The outlet badge was flush against the title in Purple theme.
- Block badge auto-insertion could not be disabled properly.
- Badge was not displaying correctly in editor preview for cart blocks. 

Other changes:

- Block theme auto-insertion is now controlled by theme mod settings.
- Margins can now be added to the message block in the editor.

## 1.1.0 (2026-10-21)

Adds modern category views for the Outlet Page, including featured category
tiles and a full menu. The tiles and menu may link to filtered views of the
outlet page by:

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
- The badge block now supports margin spacing.

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
