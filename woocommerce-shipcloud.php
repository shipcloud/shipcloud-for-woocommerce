<?php
/**
 * Plugin Name: shipcloud for WooCommerce
 * Plugin URI: https://www.wordpress.org/plugins/shipcloud-for-woocommerce/
 * Description: Integrates shipcloud shipment services to your WooCommerce shop.
 * Version: 2.0.1
 * Author: shipcloud GmbH
 * Author URI: https://shipcloud.io
 * Developer: shipcloud GmbH
 * Developer URI: https://developers.shipcloud.io
 * WC requires at least: 5.2
 * WC tested up to: 6.2.0
 * Tested up to: 5.9.1
 * Text Domain: shipcloud-for-woocommerce
 * Domain Path: /languages/
 * Copyright: © 2022 shipcloud GmbH
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the license, or
 * any later version.
 *
 * The plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see License URI.
 *
 * @package WC_Shipping_Shipcloud
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( $active_plugin === 'shipcloud-for-woocommerce/woocommerce-shipcloud.php' ) {
		$active_plugins[ $key ] = 'shipcloud-for-woocommerce/woocommerce-shipping-shipcloud.php';
	}
}
update_option( 'active_plugins', $active_plugins );
