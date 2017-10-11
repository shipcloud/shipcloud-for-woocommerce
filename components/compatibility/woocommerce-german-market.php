<?php
/**
 * WooCommerce German Market v2
 *
 * Please read the function documentations to find out which problems were solved here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Wrong context!' );
}

/**
 * Fix naming of cash on delivery payment.
 *
 * The cash on delivery payment gateway is simply named "cod" in WooCommerce.
 * WGM renames this to "cash_on_delivery".
 *
 * @see \WooCommerce_Shipcloud::FILTER_GET_COD_ID
 *
 * @return string
 */
function wcsc_compat_woocommerce_german_market_cod() {
	return 'cash_on_delivery';
}

add_filter( WooCommerce_Shipcloud::FILTER_GET_COD_ID, 'wcsc_compat_woocommerce_german_market_cod' );
