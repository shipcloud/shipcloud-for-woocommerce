<?php
/**
 * WooCommerce Shipping Classes
 * Class which adds additional functions to Shipping Classes
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2016 (support@awesome.ug)
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

class WC_Shipcloud_Shippig_Classes
{

	/**
	 * The Single instance of the class
	 *
	 * @var object $_instance
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Construct
	 *
	 * @since 1.0.0
	 */
	private function __construct()
	{
		$this->init_hooks();
	}

	/**
	 * Initializing functions
	 *
	 * @since 1.0.0
	 */
	public function init_hooks()
	{
		add_action( 'product_shipping_class_edit_form_fields', array( $this, 'shipping_class_edit_form_fields' ), 10, 2 );
		add_action( 'edited_product_shipping_class', array( $this, 'shipping_class_edit_form_fields_save' ), 10, 1 );

		add_action( 'product_shipping_class_add_form_fields', array( $this, 'shipping_class_add_form_fields' ), 10, 1 );
		add_action( 'create_product_shipping_class', array( $this, 'shipping_class_edit_form_fields_save' ), 10, 1 );
	}

	/**
	 * Main Instance
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Selecting Parcel for shipping class on editing Shipment Class
	 *
	 * @param $tag
	 * @param $taxonomy
	 *
	 * @since 1.0.0
	 * @todo Do we need params here?
	 */
	public function shipping_class_edit_form_fields( $tag, $taxonomy )
	{
		$term_id = $_GET[ 'tag_ID' ]; // $tag doesn't work really, so use $_GET[ 'tag_ID' ]

		$width = get_option( 'shipping_class_' . $term_id . '_shipcloud_width' );
		$height = get_option( 'shipping_class_' . $term_id . '_shipcloud_height' );
		$length = get_option( 'shipping_class_' . $term_id . '_shipcloud_length' );
		$weight = get_option( 'shipping_class_' . $term_id . '_shipcloud_weight' );

		$html = '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row" colspan="2">';
		$html .= '<h3>' . __( 'Shipment Settings', 'woocommerce-shipcloud' ) . '</h3>';
		$html .= '</th>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_length">' . __( 'Length', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_length" value="' . $length . '" /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_width">' . __( 'Width', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_width" value="' . $width . '" /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Height', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_height" value="' . $height . '" /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr class="form-field shipcloud-parcel-form-field">';
		$html .= '<th scope="row">';
		$html .= '<label for="shipcloud_parcel_weight">' . __( 'Weight', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '</th>';
		$html .= '<td>';
		$html .= '<input type="text" name="shipcloud_parcel_weight" value="' . $weight . '" /> ' . __( 'kg', 'woocommerce-shipcloud' );
		$html .= '</td>';
		$html .= '</tr>';

		echo $html;
	}

	/**
	 * Selecting Parcel for shipping class on adding Shipment Class
	 *
	 * @since 1.0.0
	 */
	public function shipping_class_add_form_fields()
	{

		$html = '<h4>' . __( 'Shipment Settings', 'woocommerce-shipcloud' ) . '</h4>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_length">' . __( 'Length', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_length" /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_width">' . __( 'Width', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_width" /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Height', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_height"  /> ' . __( 'cm', 'woocommerce-shipcloud' );
		$html .= '</div>';
		$html .= '<div class="form-field shipment-settings">';
		$html .= '<label for="shipcloud_parcel_height">' . __( 'Weight', 'woocommerce-shipcloud' ) . '</label>';
		$html .= '<input type="text" name="shipcloud_parcel_weight" /> ' . __( 'kg', 'woocommerce-shipcloud' );
		$html .= '</div>';
		echo $html;
	}

	/**
	 * Saving Shipping Class data on editing Shipment Class
	 *
	 * @param int $term_id Term ID
	 *
	 * @since 1.0.0
	 */
	public function shipping_class_edit_form_fields_save( $term_id )
	{
		$parcel_length = $_POST[ 'shipcloud_parcel_length' ];
		$parcel_width  = $_POST[ 'shipcloud_parcel_width' ];
		$parcel_height = $_POST[ 'shipcloud_parcel_height' ];
		$parcel_weight = $_POST[ 'shipcloud_parcel_weight' ];

		update_option( 'shipping_class_' . $term_id . '_shipcloud_length', $parcel_length );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_width', $parcel_width );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_height', $parcel_height );
		update_option( 'shipping_class_' . $term_id . '_shipcloud_weight', $parcel_weight );
	}
}

WC_Shipcloud_Shippig_Classes::instance();