<?php
/**
 * WooCommerce Core Component
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2017 (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

class WCSC_Woo extends WCSC_Component
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function __construct()
	{
		$this->name = __( 'WooCommerce functions', 'shipcloud-for-woocommerce' );
		$this->slug = 'woo';

		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
		add_filter( 'woocommerce_settings_saved', array( $this, 'woocommerce_settings_saved' ) );
	}

	public function woocommerce_settings_saved() {
        $this->check_for_active_webhook();

		$options = get_option( 'woocommerce_shipcloud_settings', array() );

		$allowed_carriers = $options['allowed_carriers'];

		return;
	}

	/**
	 * Adding Shipping Method
	 *
	 * @param $methods
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_shipping_method( $methods )
	{
		if( class_exists( 'WC_Shipcloud_Shipping' ) )
		{
			$methods[ 'shipcloud' ] = 'WC_Shipcloud_Shipping';
		}
        else
        {
            WooCommerce_Shipcloud::admin_notice( sprintf( __( 'Could not load %s.', 'shipcloud-for-woocommerce' ), 'WC_Shipcloud_Shipping' ), 'error' );
        }

		return $methods;
	}

    /*
     * Check if webhook option in settings got (de)activated and
     * either create or delete catch all webhook afterwards
     *
     * @since 1.8.0
     */
    private function check_for_active_webhook() {
        if ( wcsc_is_settings_screen() ) {
            $plugin_settings = get_option( 'woocommerce_shipcloud_settings' );
            $webhook_id = get_option( 'woocommerce_shipcloud_catch_all_webhook_id' );
            $api = _wcsc_container()->get( '\\Woocommerce_Shipcloud_API' );

            if (isset($_POST['woocommerce_shipcloud_webhook_active']) && !$webhook_id) {
              // create catch all webhook at shipcloud
              $webhook = $api->create_webhook();
              if ( is_wp_error( $webhook ) ) {
                  $plugin_settings['webhook_active'] = 'no';
                  update_option('woocommerce_shipcloud_settings', $plugin_settings);
                  $plugin_settings = get_option( 'woocommerce_shipcloud_settings' );
                  WC_Admin_Settings::add_error(sprintf( __( 'Could not create webhook: %s', 'shipcloud-for-woocommerce' ), $webhook->get_error_message() ));
              } else {
                  error_log( 'shipcloud-for-woocommerce: Created webhook with id: '.$webhook['id']);
              }
            } elseif (!isset($_POST['woocommerce_shipcloud_webhook_active']) && $webhook_id) {
              // delete webhook at shipcloud
              if (isset($webhook_id)) {
                  WooCommerce_Shipcloud::clear_admin_notices();
                  $response = $api->delete_webhook($webhook_id);
                  if ( is_wp_error( $response ) ) {
                      error_log( 'shipcloud-for-woocommerce: Could not delete webhook. Message: ' . $webhook->get_error_message() );
                      WC_Admin_Settings::add_error(sprintf( __( 'Could not delete webhook: %s', 'shipcloud-for-woocommerce' ), $webhook->get_error_message() ));
                  }
              }
          }
        }
    }

	/**
	 * Including Files
	 *
	 * @since 1.0.0
	 */
	protected function includes()
	{
		require_once( __DIR__ . '/shipping-method.php' );

		if ( wcsc_is_enabled() )
		{
			require_once( __DIR__ . '/order.php' );
			require_once( __DIR__ . '/order-bulk.php' );
			require_once( __DIR__ . '/shipping-classes.php' );

			// Shipment Listener for WebHook Calls
			add_action( 'woocommerce_api_shipcloud', array( 'WC_Shipcloud_Shipping', 'shipment_listener' ) );

			// Additional Shipment calculation Field
			add_action( 'woocommerce_shipping_calculator_enable_city', array( 'WC_Shipcloud_Shipping', 'add_calculate_shipping_form_fields' ) );
			add_action( 'woocommerce_calculated_shipping', array( 'WC_Shipcloud_Shipping', 'add_calculate_shipping_fields' ) );
		}
	}
}

wcsc_load_component( 'WCSC_Woo' );
