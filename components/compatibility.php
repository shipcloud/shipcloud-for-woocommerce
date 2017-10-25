<?php

/**
 * Watch for specific plugins and load compatibility if given.
 */
function wcsc_plugin_compatibility() {
	foreach ( wp_get_active_and_valid_plugins() as $item ) {
		// Turn "woocommerce-german-market/woocommerce-german-market.php" to slug "woocommerce-german-market"
		$slug = basename( dirname( $item ) );

		// Possible compatibility drop-in
		$drop_in = __DIR__ . DIRECTORY_SEPARATOR . 'compatibility' . DIRECTORY_SEPARATOR . $slug . '.php';

		if ( ! file_exists( $drop_in ) ) {
			// Nothing for this plugin so we ignore it.
			continue;
		}

		require_once $drop_in;
	}

	/**
	 * Generic API for other plugins to hop in and change what we did.
	 *
	 * After WooCommerce Shipcloud has registered some function for compatibility reasons
	 * we allow other plugins to manipulate this right afterwards.
	 */
	do_action( 'wcsc_plugin_compatibility' );
}

add_action( 'plugins_loaded', 'wcsc_plugin_compatibility' );
