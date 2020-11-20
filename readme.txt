=== shipcloud for WooCommerce ===
Contributors: mahype, awesome-ug, andrecedik, screamingdev
Tags: woocommerce, shipping, tracking, stamps
Requires at least: 4.2.0
Tested up to: 5.4.2
Stable tag: 1.14.0
Requires PHP: 7.2.3

This plugin integrates [shipcloud](http://bit.ly/shipcloud-for-woocommerce-en) into WooCommerce.

== Description ==

shipcloud is the only shipping service provider you need! With this plugin you can create shipping labels for all popular shipping carriers in Germany right out of the WooCommerce admin panel. Without the need to copy or upload your order data into another external system (no CSV upload necessary). Our direct integration with the shipcloud system not only enables you to create shipping labels, you’re also able to track your shipments for free. This way you’ll always know where your orders are currently at.

Works for WooCommerce 2.6 as well as for WooCommerce 3.x.

= All the benefits shipcloud offers you =

* manage shipping labels from the WooCommerce admin panel - no need to copy data into or work in another tool
* save a lot of time when creating shipping labels
* choose the carrier(s) from the most popular ones that suit you best
* let your customers decide right in the checkout, which shipping carrier they’d like to receive their order from
* use your own carrier contracts or utilize shipcloud contracts for better quotes
* create shipping labels right from a single WooCommerce order or use batch label creation from the orders overview page
* create return labels
* customized tracking page: shipcloud offers a tracking page for every shipment where your customers can track their shipment. You can customize this tracking page to follow your company design guidelines
* customized status emails: Always keep your customers in the loop by sending customized shipping status emails that follow your company design guidelines

= Complete list of supported shipping carriers =

Find all the supported carriers at the [shipcloud.io website](https://www.shipcloud.io/en/partner/carriers)

= Additional services* =

* Advance notice
* Bulky items
* Cash on delivery
* Delivery date
* Delivery time
* Drop authorization
* DHL Packstation
* DHL Postfiliale
* DHL Premium International
* DHL Visual Age Check
* Drop authorization
* GLS Guaranteed24Service
* GLS FlexDeliveryService
* GO! Delivery note
* Higher insurance
* Saturday delivery
* UPS Adult Signature
* Visual age check

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

== Latest changelog ==

= 1.14.0 =
* Added: DHL additional service endorsement, named person only, parcel outlet routing
* Added: Option to select a standard parcel template
* Added: Option to define an api timeout value
* Added: Option to define if shipments will be picked up by DHL Express regularly
* Enhanced: Added the option to log versions
* Enhanced: Use product title instead of description for customs declarations
* Enhanced: Changed storing downloads and notices from sessions to transients
* Fixed: Pickup request for a single shipment
* Fixed: Updating customs declarations
* Fixed: Logging unsupported bulk requests

See the [github releases section](https://github.com/shipcloud/shipcloud-for-woocommerce/releases) for older releases.
