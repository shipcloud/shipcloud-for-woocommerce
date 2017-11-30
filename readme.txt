=== shipcloud for WooCommerce ===
Contributors: mahype, awesome-ug, andrecedik, screamingdev
Tags: woocommerce, shipping, tracking, stamps
Requires at least: 4.2.0
Tested up to: 4.8.2
Stable tag: 1.5.1
Requires PHP: 5.4

This plugin integrates [shipcloud](http://bit.ly/shipcloud-for-woocommerce-en) into WooCommerce.

== Description ==

shipcloud is the only shipping service provider you need! With this plugin you can create shipping labels for all popular shipping carriers in Germany right out of the WooCommerce admin panel. Without the need to copy or upload your order data into another external system (no CSV upload necessary). Our direct integration with the shipcloud system not only enables you to create shipping labels, you’re also able to track your shipments for free. This way you’ll always know where your orders are currently at.

Works for WooCommerce 2.6 as well as for WooCommerce 3.0 and above.

= All the benefits shipcloud offers you =

* manage shipping labels from the WooCommerce admin panel - no need to copy data into or work in another tool
* save a lot of time when creating shipping labels
* choose the carrier(s) from the most popular ones that suit you best
* same day delivery shipping possible - just use Liefery as your shipping carrier
* let your customers decide right in the checkout, which shipping carrier they’d like to
receive their order from
* use your own carrier contracts or utilize shipcloud contracts for better quotes
* create shipping labels right from a single WooCommerce order or use batch label creation from the orders overview page
* create return labels and enclose them with each shipment
* customized tracking page: shipcloud offers a tracking page for every shipment where your customers can track their shipment. You can customize this tracking page to follow your company design guidelines
* customized status emails: Always keep your customers in the loop by sending customized shipping status emails that follow your company design guidelines

= Complete list of supported shipping carriers =

* DHL
* DHL Express
* Deutsche Post
* UPS
* Hermes
* GLS
* MyDPD Pro
* MyDPD Business (formerly iloxx)
* FedEx
* TNT
* GO! (General Overnight)
* Liefery

= Additional services* =

* Bulky items
* Saturday delivery
* Cash on delivery
* Advance notice
* Visual age check
* Drop authorization
* DHL Packstation
* DHL Postfiliale
* Higher insurance

= shipcloud WooCommerce customers =

A multitude of WooCommerce customers succussfully use shipcloud as their shipping service provider.
Here are a few of them:

* [einhorn.my](https://www.shipcloud.io/de/company/press/references/mit-shipcloud-koennen-wir-reibungsverluste-reduzieren?utm_source=wordpress&utm_medium=woocommerce&utm_campaign=pluginbeschreibung)
* [grillido.de](https://www.shipcloud.io/de/company/press/references/wenn-s-um-die-wurst-geht-shipcloud?utm_source=wordpress&utm_medium=woocommerce&utm_campaign=pluginbeschreibung)
* [bertrand.bio](https://www.shipcloud.io/de/company/press/references/unsere-erfahrungen-waren-durchweg-positiv-sonst-waeren-wir-kein-treuer-kunde?utm_source=wordpress&utm_medium=woocommerce&utm_campaign=pluginbeschreibung)

= One-stop national and international shipping =

Using shipcloud you can start sending international shipments from Germany right away. Seize the opportunity to reach customers outside of Germany.

= If you want to use shipcloud from outside of Germany =

* It's possible to create shipping labels from Austria, Switzerland and the Low Countries by using your own DPD business contract.
* Create shipments from outside the EU by using your own UPS business contract.

= Demo-Videos =

We've got a couple of videos explaining the usage of the plugin.

** Setting up parcel templates **

https://youtu.be/NgYNcHfrnDQ

** Creating a single shipping label for an order **

https://youtu.be/DQd6NC3VJEQ

Download the WooCommerce Plugin now, [register at shipcloud.io](http://bit.ly/shipcloud-for-woocommerce-en), enter your api key and start creating shipping labels right away!

\* Additional services are dependent on the carrier and contract which is being used

== Installation ==

1. Upload the folder `shipcloud-for-woocommerce` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. [Register an account](http://bit.ly/shipcloud-for-woocommerce-en) with shipcloud
4. Go to the `WooCommerce/Settings/Shipping` tab in the WordPress Admin, select `shipcloud`, add your API Key and put in the rest of the settings
5. Be sure that you have setup the shipment related settings (length, height, width and weight) in your products
6. or if you are using Shipping Classes, add the settings there
7. After setting up, your new orders will have a box called `shipcloud shipping center`
8. There you can calculate prices for your parcels, create & download labels or view their tracking data

You can also follow this simple instruction video (in German):
https://youtu.be/HE3jow15x8c

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

= 1.6.0 =
* Enhanced: API connections optimized/centralized.

= 1.5.1 =
* Enhanced: added Belgium ISO country codes so users can send shipments via DPD to Belgium
* Enhanced: Show minimum required versions for PHP and WooCommerce.
* Enhanced: detect installed WooCommerce German Market plugin and detect cash on delivery orders differently
* Fixed: `care_of` now displayed in shipment data and transmitted when updating a shipment
* Fixed: Show error messages in backend when creating a label out of prepared ones fails.
* Fixed: Call to undefined method WC_Shipcloud_Order_Bulk::sanitize_package()

= 1.5.0 =
* Enhanced: When using the [WooCommerce germanized plugin](https://wordpress.org/plugins/woocommerce-germanized/) the post number for using DHL Packstation will be used for creating shipping labels instead of `shipping_address_2`
* Enhanced: Labels for cash on delivery can now be created.
* Enhanced: Prepared labels can be edited before creating them (while editing an order).

= 1.4.3 =
* Fixed: Added whitespace between first and last name as well as street name and number when displaying a shipment
* Fixed: `notification_email` is now transmitted correctly

= 1.4.2 =
* Enhanced: Added direct links to German customer stories to readme.txt
* Fixed: Fatal error due to `Call to undefined method WC_Order::get_meta()` for WooCommerce 2 users
* Fixed: Parcel template names are now shown alike at all places
* Fixed: Workaround for when open_basedir restrictions are in effect
* Fixed: Prepared shipments are directly shown again in list of shipments after creation

= 1.4.1 =
* Enhanced: You'll find links to YouTube videos now in the reame which show how to setup Shipcloud.
* Enhanced: Make cURL work while "open_basedir" is active.
* Fixed: While working on a single order the shipping labels didn't get created.
* Fixed: Backend does not work properly when mbstring extension were missing.
* Fixed: While working on a single order the shipping cost couldn't be calculated.
* Fixed: Return labels wrote sender and recipient in the wrong way.

= 1.4.0 =
* Added: Support for shipcloud package types
* Added: Phone number for sender and receiver address.
* Fixed: Use `wc_format_decimal` to sanitize numeric inputs.

= 1.3.2 =
* Fixed: Correctly explode street numbers with digits in the street name
* Fixed: Start session earlier but not when headers are already sent

= 1.3.1 =
* Fixed: Postcode not taken from Settings
* Fixed: Create shipment form not fully shown
* Fixed: Invoiceaddress field "suite" not used for creating shipping label
* Fixed: Not working shipping in frontend

= 1.3.0 =
* Enhanced: Made Plugin WordPress.org ready
* Enhanced: Return single pdf after batch label creation
* Enhanced: Renamed Plugin
* Enhanced: Reworked language keys
