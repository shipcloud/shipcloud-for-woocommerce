<?php

/**
 * Uninstall routine.
 *
 * @category 	File
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

/*
 * Only remove ALL product and page data if WC_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'WC_REMOVE_ALL_DATA' ) && true === WC_REMOVE_ALL_DATA ) {
	delete_option( 'woocommerce_shipcloud_settings' );
}
