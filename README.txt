=== shipcloud for WooCommerce ===
Contributors: mahype, awesome-ug
Tags: woocommerce, shipcloud
Requires at least: 3.0.0
Tested up to: 4.3.0
Stable tag: 1.3.0

This plugin integrates shipcloud into WooCommerce.

== Description ==

Integrate your shipcloud.io account into your WooCommerce shop and start creating shipping labels
for all popular shipping carriers in Germany right out of the WooCommerce admin panel. Without the
need to copy or upload your order data into another external system (no CSV upload necessary).

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `WooCommerce/Settings/Shipping` tab in the WordPress Admin and add your API Key and put in the rest of the settings
4. Be Sure that you have setup the Shipment Settings (Length, Height, Width and Weight) in your products
5. or if you are using Shipping Classes, add the Settings there
6. After setting up, your new orders will have a new box called `shipcloud.io Shipment center`
8. There you can calculate price for your parcels, create & download labels or view the tracking data

== Changelog ==

= 1.3.0 =
* Enhanced: Made Plugin WordPress.org ready
* Enhanced: Return single pdf after batch label creation
* Enhanced: Renamed Plugin
* Enhanced: Reworked language keys

= 1.2.1 =
* Enhanced: Added missing translations
* Enhanced: Removed Woo Updatescripts
* Enhanced: Refactored Code
* Fixed: Zip code and street did not no matched between API and plugin

= 1.2.0 =
* Enhanced: Added bulk label creation
* Enhanced: Added shipment description
* Enhanced: Added packstation adress possibility

= 1.1.2 =
* Fixed: Wrong standard value for fallback price
* Fixed: Not working default value for shipping method
* Fixed: Only 5 Parcel Templates showed up

= 1.1.1 =
* Tweaked: Checking if logger is enabled within logger function
* Tweaked: Added Actionhook 'shipcloud_shipment_tracking_change' for all tracking status changes
* Fixed: Error on logging because of static call of WC_Settings_API
* Fixed: Error 500 on calling Webhook URL

= 1.1.0 =
* Enhanced: Better logging information
* Enhanced: Replaced new lines on Logging and only using Shipping method logger
* Enhanced: Added option for automatic street detection
* Enhanced: Added advanced notice for DHL
* Enhanced: Added reference number and filter for reference number
* Enhanced: Added base functionality for converting ISO language strings for DPD
* Enhanced: Added Notification Email for sending status changes to recipient
* Enhanced: Added state to addresses
* Enhanced: Added Tracking numbers in labels
* Enhanced: Added API functions wcsc_get_shipments() and wcsc_get_tracking_numbers()
* Enhanced: Added shipping zone support which have been introduced in WooCommerce 2.6.0
* Enhanced: Added option for people who only want to use label creation
* Enhanced: Added option for calculating with a virtual parcel, by adding all volume and weights
* Enhanced: Shipments are now also deletable if the have a label
* Enhanced: Only loading JS and CSS when plugin needs it
* Enhanced: Logging if API Limit is reached
* Enhanced: Optimized shipment calculation by better code structure and better performance on API Requests
* Enhanced: Created fallback options on problems with API
* Enhanced: Better street & number splitting
* Fixed: Only adding calculated parcels if there have been parcels determined
* Fixed: Wrong ISO format for DHL and DPD advanced notice
* Fixed: Shipment classes could not be edited anymore in WooCommerce 2.6.0 because of GUI change
* Fixed: Error on activation "Error: Class 'WC_Shipcloud_Shipping' not found"
* Fixed: JavaScript which stopped all JS in WP-Admin in Safari Browser
* Fixed: Changed to WordPress function get_plugin_url() instead of older own function
* Fixed: Compatibility problems with WooCommerce 2.6 parent class
* Fixed: Fatal error on activation on PHP versions lower than 5.5

= 1.0.1 =
* Fixed massive number of calls for carriers

= 1.0.0 beta 1 =
* Return services are only allowed for shop customers
* Preselection of user selected shipping method in admin
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
