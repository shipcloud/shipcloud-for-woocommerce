=== shipcloud for WooCommerce ===
Contributors: mahype, awesome-ug, andrecedik, screamingdev
Tags: woocommerce, shipping, tracking, stamps
Requires at least: 4.2.0
Tested up to: 4.9.8
Stable tag: 1.9.4
Requires PHP: 5.5

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

= Additional services* =

* Advance notice
* Bulky items
* Cash on delivery
* DHL Delivery Time
* DHL Packstation
* DHL Postfiliale
* DHL Premium International
* DHL Visual Age Check
* Drop authorization
* GLS Guaranteed24Service
* GLS FlexDeliveryService
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

== Changelog ==

= 1.10.0 =
* Enhanced: Added new service (Europaket) for DHL
* Enhanced: Added new service (Expedited) for UPS
* Enhanced: Added new package types (Disposable pallet and Euro pallet) for Cargo International
* Enhanced: Package types are now centrally defined for easier access / adjustments

= 1.9.4 =
* Fixed: Class loading of files with underscores

= 1.9.3 =
* Fixed: Duplicate calls to create shipments when using bulk action

= 1.9.2 =
* Fixed: Reworked class loading because of issues with autoloader
* Fixed: Make sure webhook_active setting is present

= 1.9.1 =
* Fixed: Bug on order overview that would cause actions to not fire

= 1.9.0 =
* Enhanced: Added notice in settings about WooCommerce 2 support EOL
* Enhanced: Added possibility to create pickup requests (only in WooCommerce 3)
* Enhanced: Added new services UPS Express 12:00 and Deutsche Post Warenpost
* Enhanced: Make it possible to define a global key to be used as reference number. Introduced shortcodes to make the key customizable
* Enhanced: Added configuration for displaying the pakadoo id within checkout (billing address)
* Enhanced: Added configuration for displaying a care of field within checkout (billing & shipping address)
* Enhanced: Added configuration for displaying a phone field within checkout (billing address)
* Fixed: Use official language for shipments with advance notice
* Fixed: Link carrier tracking number to carrier tracking page
* Fixed: Bug when updating a shipment that would cause the shipment to lose its label_url

= 1.8.2 =
* Enhanced: Show info at api url page if called via get-request
* Enhanced: Show error when shipcloud webhooks are enabled but WooCommerce api isn't
* Fixed: Check to see if on shipcloud settings page before saving settings

= 1.8.1 =
* Fixed: Labels weren't merged when using the bulk action from 2nd dropdown on shop orders page
* Fixed: Unnecessary multiple calls to create/delete a webhook
* Fixed: Error when trying to log an error while creating a webhook

= 1.8.0 =
* Added: Saturday delivery
* Added: Age based delivery (DHL visual age check, UPS adult signature)
* Added: DHL premium international
* Added: DHL preferred time
* Added: Drop authorization
* Added: GLS Guaranteed24Service
* Enhanced: Added info about where the debug log can be found
* Enhanced: Added setting to always use the calculated weight from order
* Enhanced: PHP 5.5 is now the minimum requirement
* Enhanced: Show order status tracking in user account
* Fixed: Error when trying to create the first parcel template
* Fixed: Handling of billing address for notifications when using WooCommerce 2

= 1.7.0 =
* Added: Buyers can now use their pakadoo id to determine the shipping address
* Added: Total order weight can now be used when creating shipments by selecting a checkbox
* Added: WooCommerce version check headers
* Enhanced: Added more logging (when debug log is enabled) to make support cases can be answered easier.
* Enhanced: Carrier specific handling of cash on delivery attributes when using GLS or UPS as carrier
* Enhanced: Service and package type are now preselected in dropdowns when selecting a new carrier

= 1.6.6 =
* Fixed: Creating return shipping labels.

= 1.6.5 =
* Fixed: Creating shipping labels from prepared shipments not working because of address id.

= 1.6.4 =
* Enhanced: Rework handling of WP_Error when deleting a shipment
* Enhanced: Added method to shipment model so we can output error messages to the UI
* Fixed: Standard carrier caused problems in checkout when buyer was selecting the shipping carrier.
* Fixed: Added handling of undefined parcel, sender and shipment data
* Fixed: Added backwards compatibility for getting the payment method (to check for cash on delivery)
* Removed: Standard carrier from settings.

= 1.6.3 =
* Enhanced: Removed buttons for creating/preparing return shipments, because you can select returns as a service
* Enhanced: Make it possible to supply a reference_number before creating a shipment
* Fixed: empty destination zip_code in checkout
* Fixed: missing tracking url link

= 1.6.2 =
* Enhanced: Added more countries and their respective languages to the I18n Mapping.
* Fixed: missing zip_code in sender input field
* Fixed: care_of not shown when editing prepared shipments
* Fixed: (Re)Added an input field for providing a value to book a higher insurance with the carrier
* Fixed: Error message when ISO language code couldn't be determined

= 1.6.1 =
* Fixed: Problems with shop guests and cart usage fixed.

= 1.6.0 =
* Enhanced: Shipment interface to prepare/create labels for an order.
* Enhanced: Add action link so users can jump from plugins page directly to the plugin settings
* Enhanced: API connections optimized/centralized.
* Fixed: Flush carriers on plugin (de-)activation to manually invalidate the cache.
* Fixed: Streets with a ranged number (like "Middlestreet 162 - 164") are now parsed correctly.
* Fixed: Prepared shipments can be modified again.
* Fixed: Prepared shipments can be deleted.

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
