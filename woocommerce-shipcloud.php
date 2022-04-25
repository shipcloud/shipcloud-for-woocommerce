<?php

/**
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
