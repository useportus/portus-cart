=== Portus Cart for WooCommerce ===
Contributors: portusoficial
Tags: woocommerce, side cart, ajax cart, cart, checkout
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 3.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Responsive AJAX side cart for WooCommerce with coupons, item removal, quantity controls and a floating cart button.

== Description ==

Portus Cart for WooCommerce adds a fast, responsive side cart to WooCommerce stores. It helps customers review cart items, update quantities, apply coupons, remove products and continue to checkout without leaving the current page.

The plugin focuses on the essential cart workflow:

* Responsive side cart panel.
* Customizable floating cart button with four local SVG icons, sizes, shapes, colors, counter positions and responsive visibility.
* Automatic opening after WooCommerce AJAX add to cart.
* Quantity updates without reloading the page.
* Apply and remove WooCommerce coupons without reloading the page.
* Remove cart items without reloading the page.
* Checkout and cart page links.
* Low stock alerts.
* Admin settings for colors, button labels, positioning and compatibility.
* Basic plugin health screen for environment checks.
* Scoped frontend styles to reduce theme conflicts.

Portus Cart uses WordPress and WooCommerce APIs, WooCommerce cart fragments and prefixed CSS classes for the plugin interface. All features included in this WordPress.org package are available without a license key.

== Requirements ==

* WordPress 6.0 or newer.
* PHP 7.4 or newer.
* WooCommerce active.
* A theme that prints the standard WordPress footer hook.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/portus-cart-for-woocommerce/` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Make sure WooCommerce is installed and active.
4. Open Portus Cart in the WordPress admin.
5. Configure the cart title, floating button, colors and compatibility options.
6. Add a WooCommerce product to the cart and test the side cart on desktop and mobile.

== Configuration ==

After activation, open the Portus Cart settings page to adjust:

* Cart title.
* Floating cart button icon, size, shape, colors, counter, responsive visibility and spacing.
* Primary and accent colors.
* Checkout, cart and empty cart button labels.
* Optional custom URL for the empty cart button.
* Coupon field follows the standard WooCommerce coupon setting.
* Automatic opening after AJAX add to cart.
* Panel width, z-index and overlay opacity.
* Low stock notices and basic checkout behavior.

The default settings are designed to work with most WooCommerce themes without additional code.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. Portus Cart for WooCommerce requires WooCommerce to be active.

= Does it work with any theme? =

The plugin is built with scoped CSS and WordPress/WooCommerce hooks. It also includes compatibility settings for z-index, panel width, overlay opacity and floating button position.

= Does the side cart open automatically after adding a product? =

Yes. By default, the side cart listens to the standard WooCommerce AJAX `added_to_cart` event. This behavior can be turned off in the compatibility settings.

= Can customers update quantity without reloading the page? =

Yes. Quantity updates and remove item actions use AJAX and refresh the side cart totals automatically.

= Why does the coupon field not appear? =

The coupon field follows the WooCommerce coupon setting. Enable coupons in WooCommerce settings if you want customers to apply coupon codes from the side cart.

= Does the plugin replace the WooCommerce checkout? =

No. The checkout button sends customers to the standard WooCommerce checkout page.

= Can I change the cart colors and button labels? =

Yes. The plugin includes admin settings for the cart title, primary color, accent color, checkout button label and floating button behavior.

= Will it work with cache plugins? =

In most stores, yes. If a cache or optimization plugin delays WooCommerce cart fragments or AJAX scripts, exclude WooCommerce cart fragments and the Portus Cart JavaScript from aggressive delay rules.

== Privacy ==

Portus Cart for WooCommerce does not send customer data to an external service. The plugin works with the local WordPress and WooCommerce cart session.

Cart content, quantities and totals are handled by WooCommerce. Store owners should review their WooCommerce privacy policy and payment/shipping integrations separately.

== Screenshots ==

1. Side cart panel with products, quantity controls, coupon field, totals and checkout action.
2. Empty side cart state with a clear action to continue shopping.
3. Mobile side cart experience in a phone viewport.
4. Desktop page with the side cart overlay active.
5. Visual settings for colors and cart identity.
6. About / Portus admin screen with product identity and support links.
7. Conversion settings for checkout and cart call-to-action labels.
8. Plugin health screen with WordPress, PHP and file diagnostics.

== Changelog ==

= 3.4.0 =
* Adds four local Bootstrap SVG icons and complete visual controls for the floating cart button.
* Adds button and icon sizes, circular or rounded-square shape, colors and counter positioning.
* Adds independent desktop and mobile visibility with a live device preview.
* Preserves the previous floating button appearance as the default after upgrading.

== Upgrade Notice ==

= 3.4.0 =
Adds floating button personalization while preserving the previous appearance by default.
