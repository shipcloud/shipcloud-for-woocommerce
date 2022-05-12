# Changelog

## 2.0.5

### Enhanced
- updated shipcloud-php to version 0.0.2

### Fixed
- Bug that caused addresses without a last name to throw an error

## 2.0.4

### Enhanced
- update fontawesome from 5.15.4 to 6.1.1

### Fixed
- removed a check for user permissions that was throwing errors for some users

## 2.0.3

### Fixed
- Removed unnecessary `id` field from customs declaration data
- Error due to missing API key

## 2.0.2

### Enhanced
- Added some missing translations

### Fixed
- Error due to missing additional services

## 2.0.1

### Fixed
- Error due to invalid header

## 2.0.0
This is a complete code rewrite which also incorporates the new shipcloud PHP SDK.

### Added
- DPD additional service limited quantities
- Entry to the wordpress main menu for direct access to shipcloud features

### Enhanced
- Multiple carrier + service combinations can now be defined for each shipping zone. Each of them
  will be displayed to the buyer within the checkout, based on the address they've entered.

### Removed
- Automatic price calculation in checkout

## 1.14.3
### Enhanced
  -  New plugin description
  -  Declared support for WordPress up to 5.7.2 and WooCommerce up to 5.3.0

## 1.14.2
### Fixed
- Added check to see if plugin settings are present
- Use static api timeout value of 10 seconds if not defined in plugin settings
- A bug that used the wrong value for weight, when updating a shipment
- Added an extra check to see of properties for advance notice are empty

## 1.14.1
### Enhanced
- Removed handling of "customer serviced" services

### Fixed
- Shipping method names for carriers whos names are two parts
- Shipments that have been deleted at shipcloud can now be removed from the database

## 1.14.0
### Added
- DHL additional service endorsement, named person only, parcel outlet routing
- Option to select a standard parcel template
- Option to define an api timeout value
- Option to define if shipments will be picked up by DHL Express regularly

### Enhanced
- Added the option to log versions
- Use product title instead of description for customs declarations
- Changed storing downloads and notices from sessions to transients

### Fixed
- Pickup request for a single shipment
- Updating customs declarations
- Logging unsupported bulk requests

## 1.13.0
### Added
- DHL services Warenpost and Prio
- DPAG service Warenpost (with signature)
- Option to not create a shipping label (during bulk processing) when there is already one present

### Enhanced
- Submit button for creating labels in bulk is now being disabled upon click to prevent double clicks
- Better check to see if we're on an order page
- Check to see if we have an order when getting the global reference number
- Skip checking product attributes for customs declarations if product info is missing from order line items
- Return carrier names in sorted order

### Fixed
- Added handling of unspecified weights for products
- Return an empty array when there are no additional services
- Getting the global reference number for bulk processing of shipments
- Notification email checkbox got overwritten by global configuration
- Bug where timestamps couldn't be parsed

## 1.12.2
### Fixed
- Making sure advance_notice is only transmitted when it's defined

## 1.12.1
### Fixed
- Getting carriers from shipcloud API on every order detail page call

## 1.12.0
### Added
- GLS services (Express 08:00, Express 09:00, Express 10:00, Express 12:00, Pick&ShipService)
- GO! additional services delivery date, delivery note, delivery time
- Deutsche Post service Warenpost (untracked)

### Enhanced
- Additional service 'advance_notice' can now be (de-)activated while creating/editing shipping labels
- Only the additional services returned by shipcloud will be shown.
- Confirmed support for Wordpress 5.4.2 and WooCommerce 3.9.2
- Only show configured carriers for creating shipments in admin panel
- Carriers now get reloaded from shipcloud api when saving the plugin settings

### Fixed
- detection of street number and zip code entries

### Removed
- DHL no longer supports the additional service delivery_time
- Option to (de-)activate the plugin from settings page

## 1.11.1
### Enhanced
- Confirmed support for Wordpress 5 and WooCommerce 3.5.5

## 1.11.0
### Added
- Supply customs declaration data to be able to receive a CP 71 / CN23 form in return
- New package type (Large Parcel) for Cargo International

## 1.10.0
### Added
- New service (Europaket) for DHL
- New service (Expedited) for UPS
- New package types (Disposable pallet and Euro pallet) for Cargo International

### Enhanced
- Package types are now centrally defined for easier access / adjustments

### Fixed
- Dropdowns will now be preselected with correct values when editing parcel templates

## 1.9.4
### Fixed
- Class loading of files with underscores

## 1.9.3
### Fixed
- Duplicate calls to create shipments when using bulk action

## 1.9.2
### Fixed
- Reworked class loading because of issues with autoloader
- Make sure webhook_active setting is present

## 1.9.1
### Fixed
- Bug on order overview that would cause actions to not fire

## 1.9.0
### Enhanced
- Added notice in settings about WooCommerce 2 support EOL
- Added possibility to create pickup requests (only in WooCommerce 3)
- Added new services UPS Express 12:00 and Deutsche Post Warenpost
- Make it possible to define a global key to be used as reference number. Introduced shortcodes to make the key customizable
- Added configuration for displaying the pakadoo id within checkout (billing address)
- Added configuration for displaying a care of field within checkout (billing & shipping address)
- Added configuration for displaying a phone field within checkout (billing address)

### Fixed
- Use official language for shipments with advance notice
- Link carrier tracking number to carrier tracking page
- Bug when updating a shipment that would cause the shipment to lose its label_url

## 1.8.2
### Enhanced
- Show info at api url page if called via get-request
- Show error when shipcloud webhooks are enabled but WooCommerce api isn't

### Fixed
- Check to see if on shipcloud settings page before saving settings

## 1.8.1
### Fixed
- Labels weren't merged when using the bulk action from 2nd dropdown on shop orders page
- Unnecessary multiple calls to create/delete a webhook
- Error when trying to log an error while creating a webhook

## 1.8.0
### Added
- Saturday delivery
- Age based delivery (DHL visual age check, UPS adult signature)
- DHL premium international
- DHL preferred time
- Drop authorization
- GLS Guaranteed24Service

### Enhanced
- Added info about where the debug log can be found
- Added setting to always use the calculated weight from order
- PHP 5.5 is now the minimum requirement
- Show order status tracking in user account

### Fixed
- Error when trying to create the first parcel template
- Handling of billing address for notifications when using WooCommerce 2

## 1.7.0
### Added
- Buyers can now use their pakadoo id to determine the shipping address
- Total order weight can now be used when creating shipments by selecting a checkbox
- WooCommerce version check headers

### Enhanced
- Added more logging (when debug log is enabled) to make support cases can be answered easier.
- Carrier specific handling of cash on delivery attributes when using GLS or UPS as carrier
- Service and package type are now preselected in dropdowns when selecting a new carrier

## 1.6.6
### Fixed
- Creating return shipping labels.

## 1.6.5
### Fixed
- Creating shipping labels from prepared shipments not working because of address id.

## 1.6.4
### Enhanced
- Rework handling of WP_Error when deleting a shipment
- Added method to shipment model so we can output error messages to the UI

### Fixed
- Standard carrier caused problems in checkout when buyer was selecting the shipping carrier.
- Added handling of undefined parcel, sender and shipment data
- Added backwards compatibility for getting the payment method (to check for cash on delivery)

### Removed
- Standard carrier from settings.

## 1.6.3
### Enhanced
- Removed buttons for creating/preparing return shipments, because you can select returns as a service
- Make it possible to supply a reference_number before creating a shipment

### Fixed
- empty destination zip_code in checkout
- missing tracking url link

## 1.6.2
### Enhanced
- Added more countries and their respective languages to the I18n Mapping.

### Fixed
- missing zip_code in sender input field
- care_of not shown when editing prepared shipments
- (Re)Added an input field for providing a value to book a higher insurance with the carrier
- Error message when ISO language code couldn't be determined

## 1.6.1
### Fixed
- Problems with shop guests and cart usage fixed.

## 1.6.0
### Enhanced
- Shipment interface to prepare/create labels for an order.
- Add action link so users can jump from plugins page directly to the plugin settings
- API connections optimized/centralized.

### Fixed
- Flush carriers on plugin (de-)activation to manually invalidate the cache.
- Streets with a ranged number (like "Middlestreet 162 - 164") are now parsed correctly.
- Prepared shipments can be modified again.
- Prepared shipments can be deleted.

## 1.5.1
### Enhanced
- added Belgium ISO country codes so users can send shipments via DPD to Belgium
- Show minimum required versions for PHP and WooCommerce.
- detect installed WooCommerce German Market plugin and detect cash on delivery orders differently

### Fixed
- `care_of` now displayed in shipment data and transmitted when updating a shipment
- Show error messages in backend when creating a label out of prepared ones fails.
- Call to undefined method WC_Shipcloud_Order_Bulk::sanitize_package()

## 1.5.0
### Enhanced
- When using the [WooCommerce germanized plugin](https://wordpress.org/plugins/woocommerce-germanized/) the post number for using DHL Packstation will be used for creating shipping labels instead of `shipping_address_2`
- Labels for cash on delivery can now be created.
- Prepared labels can be edited before creating them (while editing an order).

## 1.4.3
### Fixed
- Added whitespace between first and last name as well as street name and number when displaying a shipment
- `notification_email` is now transmitted correctly

## 1.4.2
- Release date: 2017-08-17

### Enhanced
- Added direct links to German customer stories to readme.txt

### Fixed
- Fatal error due to `Call to undefined method WC_Order::get_meta()` for WooCommerce 2 users
- Parcel template names are now shown alike at all places
- Workaround for when open_basedir restrictions are in effect
- Prepared shipments are directly shown again in list of shipments after creation

## 1.4.1
- Release date: 2017-08-04

### Enhanced
- You'll find links to YouTube videos now in the reame which show how to setup Shipcloud.
- Make cURL work while "open_basedir" is active.

### Fixed
- While working on a single order the shipping labels didn't get created.
- Backend does not work properly when mbstring extension were missing.
- While working on a single order the shipping cost couldn't be calculated.
- Return labels wrote sender and recipient in the wrong way.

## 1.4.0
- Release date: 2017-08-03

### Changed
- Support for shipcloud package types
- Phone number for sender and receiver address.

### Fixed
- Use `wc_format_decimal` to sanitize numeric inputs.

## 1.3.2
- Correctly explode street numbers with digits in the street name
- Start session earlier but not when headers are already sent

## 1.3.1
- Postcode not taken from Settings
- Create shipment form not fully shown
- Invoiceaddress field "suite" not used for creating shipping label
- Not working shipping in frontend

## 1.3.0
- Made Plugin WordPress.org ready
- Return single pdf after batch label creation
- Renamed Plugin
- Reworked language keys

## 1.2.1
- Release date: 2017-06-20

### Changed
- Added missing translations
- Removed Woo Updatescripts
- Refactored Code

### Fixed
- Zip code and street did not no matched between API and plugin

## 1.2.0
- Release date: 2017-05-12

### Changed
- Added bulk label creation
- Added shipment description
- Added packstation adress possibility

## 1.1.2
- Release date: 2017-04-24

### Fixed
- Wrong standard value for fallback price
- Not working default value for shipping method
- Only 5 Parcel Templates showed up

## 1.1.1
- Release date: 2017-02-28

### Changed
- Checking if logger is enabled within logger function
- Added Actionhook 'shipcloud_shipment_tracking_change' for all tracking status changes

### Fixed
- Error on logging because of static call of WC_Settings_API
- Error 500 on calling Webhook URL

## 1.1.0
- Release date: 2016-09-08

### Changed
- Better logging information
- Replaced new lines on Logging and only using Shipping method logger
- Shipments are now also deletable if the have a label
- Only loading JS and CSS when plugin needs it
- Logging if API Limit is reached
- Optimized shipment calculation by better code structure and better performance on API Requests
- Created fallback options on problems with API
- Better street & number splitting

### Added
- Option for automatic street detection
- Advanced notice for DHL
- Reference number and filter for reference number
- Base functionality for converting ISO language strings for DPD
- Notification email for sending status changes to recipient
- State to addresses
- Tracking numbers in labels
- API functions wcsc_get_shipments() and wcsc_get_tracking_numbers()
- Shipping zone support which have been introduced in WooCommerce 2.6.0
- Option for people who only want to use label creation
- Option for calculating with a virtual parcel, by adding all volume and weights

### Fixed
- Only adding calculated parcels if there have been parcels determined
- Wrong ISO format for DHL and DPD advanced notice
- Shipment classes could not be edited anymore in WooCommerce 2.6.0 because of GUI change
- Error on activation "Error: Class 'WC_Shipcloud_Shipping' not found"
- JavaScript which stopped all JS in WP-Admin in Safari Browser
- Changed to WordPress function get_plugin_url() instead of older own function
- Compatibility problems with WooCommerce 2.6 parent class
- Fatal error on activation on PHP versions lower than 5.5

## 1.0.1
- Release date: 2016-05-18

### Fixed
- Massive number of calls for carriers

## 1.0.0
- Release date: 2016-04-14

### Added
- Initial release!

## 1.0.0 beta 1
### Changed
- Return services are only allowed for shop customers
- Preselection of user selected shipping method in admin
- Adjusted shipment service names

## 1.0.0 alpha 5
### Added
- Service 'predict' for DPD
- Shipment services
- Deletion of shipments

## 1.0.0 alpha 4
### Changed
- Finished shipment listener

## 1.0.0 alpha 3
### Added
- Shipment listener

## 1.0.0 alpha 2
### Changed
- Calculate with product or shipping class sizes instead of parcel templates
- Use singleton pattern
- Use wp_remote_ functions instead of cURL

### Added
- Selection of allowed carriers
- Automatic price calculation
- Possibility for customers to select carrier
- Standard carrier option
- Standard parcel prices if packages have not entered values to a shipping class or product

## 1.0.0 alpha 1
### Added
- First official alpha version
