=== shipcloud.io for WooCommerce ===
Contributors: mahype, awesome-ug
Tags: woocommerce, shipcloud
Requires at least: 3.0.0
Tested up to: 4.3.0
Stable tag: 1.0.2

This plugin integrates shipcloud.io into WooCommerce.

== Description ==

Integrate your shipcloid.io account to your WooCommerce shop. Create shipment labels, view tracking info and calculate your shipment prices.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `WooCommerce/Settings/Shipping` tab in the WordPress Admin and add your API Key and put in the rest of the settings
4. Be Sure that you have setup the Shipment Settings (Length, Height, Width and Weight) in your products
5. or if you are using Shipping Classes, add the Settings there
6. After setting up, your new orders will have a new box called `shipcloud.io Shipment center`
8. There you can calculate price for your parcels, create & download labels or view the tracking data

== Changelog ==

= 1.0.2 =
* Fixed problems on loading shipment method by changing to direct loading without hooks

= 1.0.1 =
* Fixed massive number of calls for carriers

= 1.0.0 beta 1 =
* Return services are only allowed for shop customers
* Preselection of user selected shipment method in admin
* Adjusted shipment service names

= 1.0.0 alpha 5 =
* Added service predict for DPD
* Added shipment services
* Deletion of shipments

= 1.0.0 alpha 4 =
* Finished Shipment Listener

= 1.0.0 alpha 3 =
* Added Shipment Listener

= 1.0.0 alpha 2 =
* Added selection of allowed Carriers
* Added automatic price calculation
* Added possibility for customers to select carrier
* Added Standard Carrier Option
* Changed from Parcel Templates to calculate with Product or Shipping Class sizes
* Added Standard Parcel Prices if packages have not entered values to a Shipping class or Product
* Changed to Singleton Pattern
* Changed from CURL to wp_remote_ functions

= 1.0.0 alpha 1 =
* First official alpha version
