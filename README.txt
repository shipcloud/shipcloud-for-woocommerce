=== shipcloud for WooCommerce ===
Contributors: mahype, awesome-ug, andrecedik, screamingdev
Tags: woocommerce, shipcloud
Requires at least: 3.0.0
Tested up to: 4.8.0
Stable tag: 1.3.1

This plugin integrates shipcloud into WooCommerce.

== Description ==

shipcloud is the only shipping service provider you need! With this plugin you can create shipping labels for all popular shipping carriers in Germany right out of the WooCommerce admin panel. Without the need to copy or upload your order data into another external system (no CSV upload necessary). Our direct integration with the shipcloud system not only enables you to create shipping labels, you’re also able to track your shipments for free. This way you’ll always know where your orders are currently at.

All the benefits shipcloud offers you:

* manage shipping labels from the WooCommerce admin panel - no need to copy data into or work in another tool
* save a lot of time when creating shipping labels
* choose the carrier(s) from the most popular ones that suit you best
* same day delivery shipping possible - just use Liefery as your shipping carrier
* let your customers decide right in the checkout, which shipping carrier they’d like to
receive their order from
* use your own carrier contracts or utilize shipcloud contracts for better quotes
* create shipping labels right from a single WooCommerce order or use batch label
creation from the orders overview page
* create return labels and enclose them with each shipment
* customized tracking page: shipcloud offers a tracking page for every shipment where
your customers can track their shipment. You can customize this tracking page to
follow your company design guidelines
* customized status emails: Always keep your customers in the loop by sending
customized shipping status emails that follow your company design guidelines

Complete list of supported shipping carriers:

* DHL
* DHL Express
* Deutsche Post (Letters, Books and Goods)
* UPS
* Hermes
* GLS
* MyDPD Pro
* MyDPD Business (formerly iloxx)
* FedEx
* TNT
* GO! (General Overnight)
* Liefery

Additional services*:

* Bulky items
* Saturday delivery
* Cash on delivery
* Advance notice
* Visual age check
* Drop authorization
* DHL Packstation
* DHL Postfiliale
* Higher insurance

shipcloud WooCommerce customers:

We’ve got a lot of interesting customers who are using the shipcloud WooCommerce plugin. Here are a few of them:

* bertrand.bio
* memento-online.de
* highderm.de
* restube.eu
* humanblood.de
* city-of-shirts.de
* css-halle.de
* wedding-shoots.de
* filamentworld.de

One-stop national and international shipping
Using shipcloud you can start sending international shipments from Germany right away. Seize the opportunity to reach customers outside of Germany.
If you want to use shipcloud from outside of Germany:

● It’s possible to create shipping labels from Austria, Switzerland and the Low Countries by using your own DPD business contract.
● Create shipments from outside the EU by using your own UPS business contract.

Download the WooCommerce Plugin now, register for shipcloud, enter your api key and start creating shipping labels right away!

* Additional services are dependent on the carrier and contract which is being used

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `WooCommerce/Settings/Shipping` tab in the WordPress Admin and add your API Key and put in the rest of the settings
4. Be Sure that you have setup the Shipment Settings (Length, Height, Width and Weight) in your products
5. or if you are using Shipping Classes, add the Settings there
6. After setting up, your new orders will have a new box called `shipcloud.io Shipment center`
8. There you can calculate price for your parcels, create & download labels or view the tracking data

== Screenshots ==

1. Configure shipcloud from within the shipping settings of WooCommerce
2. Either let your customer decide, which shipping carrier should be used or do it yourself
3. Easy to use interface for creating shipping labels
4. Use ore defined templates for optimizing your workflow
5. Create parcel templates with just a few clicks
6. Get an overview of all your parcel templates
7. Batch label creation: Create multiple shipping labels with ease by using parcel templates
8. Adjust sender and/or receiver addresses base on your current use case

== Changelog ==

= 1.3.1 =
* Fixed: Postcode not taken from Settings
* Fixed: Create shipment form not fully shown
* Fixed: Invoiceaddress field "suite" not used for creating shipping label

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
