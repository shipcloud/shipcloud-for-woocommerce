# shipcloud for WooCommerce

Contributors: andrecedik, shipcloud
Tags: woocommerce, shipping, tracking, stamps
Stable tag: 2.0.5
Requires at least: 5.2.0
Tested up to: 5.9.1
Requires PHP: 7.4.2

This plugin integrates [shipcloud](http://bit.ly/shipcloud-for-woocommerce-en) into WooCommerce.

## Description
[shipcloud](http://bit.ly/shipcloud-for-woocommerce-en) is your shipping service provider for WooCommerce. Empower your shipping by providing a simple and uniform interface for all relevant eCommerce shipping carriers out of your WooCommerce admin panel.

**DHL, DHL Express, UPS, Deutsche Post, DPD, Hermes, Post AT, Post CH, GLS, GO!, ANGEL, Asendia, PARCEL.ONE, FedEx, and many more**

From standard shipping, express delivery, tracking to returns, shipcloud supports your complete shipping process from Germany. You can also benefit from the attractive shipcloud business rates for DHL, UPS and DPD.

**Install the shipcloud for WooCommerce plugin, sign up for shipcloud, enter your API key, and get started!**

### Key Features

#### All carriers in a single plugin
shipcloud for WooCommerce enables you to use all relevant eCommerce shipping carriers from a single interface. No need to copy or upload your order data into another external system (no CSV upload necessary). Stay within your WooCommerce admin panel and work from there.

#### Select "your" shipping carriers
Configure only the shipping carriers you would like to use and simplify your process.

#### Optimized shipping costs
Use attractive shipcloud shipping rates for DHL, UPS, and DPD, or integrate your own carrier contracts. This way you can always choose the best service and optimize your shipping costs.

#### Let your customers choose
Let your customers choose their favorite carrier and carrier options in the checkout process. Easily add carriers to a shipping zone and define the options such as green labels and price you would like to charge.

#### Bulk Actions
By using bulk actions, you can create and print shipping labels for multiple orders at the same time as well as requesting pickup by the carrier.

#### International shipping
shipcloud for WooCommerce enables you to send cross-border shipments without any hassle. Customs declaration documents will be generated for you so you can send shipments to Switzerland, the United Kingdom, and many more (cross-border) destinations.

#### Always know where your shipments are
Increase customer satisfaction thanks to multi-carrier-tracking, individual tracking pages and automated notification e-mails in your store design.

#### Simplify returns management
Creating a return shipping label through shipcloud for WooCommerce is very easy. Just select the service “returns” when creating a shipping label or offer your customers an individualized return portal. Both options are possible with shipcloud. You can even decide to provide the returns shipping label either as a PDF file or a QR code.

#### Excellent service
In order to guarantee the highest possible level of convenience, shipcloud takes care of all updates and service enhancements of the carriers you use and thus keeps all integrations up to date.

### Supported Carriers
* ANGEL
* Asendia
* Cargo International
* Deutsche Post
* DHL
* DHL Express
* DPD
* GLS
* GO!
* Hermes
* PARCEL.ONE
* Post Austria
* Post Switzerland
* UPS
* … and more

### Supported Additional Services
* Additional Insurance
* Advance Notice (via SMS / eMail)
* Age Verification
* Bulky Shipments
* Cash On Delivery
* Delivery Date
* Delivery Time
* DHL Endorsement
* DHL Packstation
* DHL Postfiliale
* DHL Premium International
* DPD Limited Quantities
* Drop Authorization
* GLS Guaranteed24Service
* GLS FlexDeliveryService
* Saturday Delivery

## Screenshots
1. Configure shipcloud from within the shipping settings of WooCommerce
2. Add your favorite carrier and service combination to the shipping zones and
3. Let your customers decide within the checkout, which carrier & service should be used
4. Easy to use interface for creating shipping labels
5. Create predefined parcel templates for ease of use
6. Batch label creation: Create multiple shipping labels with ease by using parcel templates
7. Adjust sender and/or receiver addresses base on your current use case
8. Let your customers see tracking for their order from within their shop account

## Changelog

### 2.0.0
This is a complete code rewrite which also incorporates the new shipcloud PHP SDK.

#### Added
- DPD additional service limited quantities
- Entry to the wordpress main menu for direct access to shipcloud features

#### Enhanced
- Multiple carrier + service combinations can now be defined for each shipping zone. Each of them
  will be displayed to the buyer within the checkout, based on the address they've entered.

#### Removed
- Automatic price calculation in checkout
