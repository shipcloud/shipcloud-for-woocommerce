<?php
/**
 * shipcloud for WooCommerce Core Functions
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package shipcloudForWooCommerce/Core
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

/**
 * Adds a Parcel Template
 *
 * @param string $carrier The Carrier Name
 * @param int    $width   The width in cm
 * @param int    $height  The height in cm
 * @param int    $length  The length in cm
 * @param int    $weight  The weight in kg
 *
 * @return int|WP_Error Returns the new Parcel Template ID or on failure WP_Error
 *
 * @since   1.0.0
 */
function wcsc_add_parceltemplate( $carrier, $width, $height, $length, $weight )
{
	$post = array(
		'post_title'  => $carrier . ' - ' . $width . 'x' . $height . 'x' . $length . __( 'cm', 'shipcloud-for-woocommerce' ) . ' ' . $weight . __( 'kg', 'shipcloud-for-woocommerce' ),
		'post_status' => 'publish',
		'post_type'   => 'sc_parcel_template'
	);

	$post_id = wp_insert_post( $post );

	add_post_meta( $post_id, 'carrier', $carrier );
	add_post_meta( $post_id, 'width', $width );
	add_post_meta( $post_id, 'height', $height );
	add_post_meta( $post_id, 'length', $length );
	add_post_meta( $post_id, 'weight', $weight );

	add_action( 'wcsc_add_parceltemplate', $post_id );

	return $post_id;
}

/**
 * Deletes a Parcel Template
 *
 * @param $template_id
 *
 * @return array|false|WP_Post
 *
 * @since   1.0.0
 */
function wcsc_delete_parceltemplate( $template_id )
{
	add_action( 'wcsc_delete_parcel_template', $template_id );

	return wp_delete_post( $template_id );
}

/**
 * Gets a Parcel Template
 *
 * @param $template_id
 *
 * @return mixed
 *
 * @since   1.0.0
 */
function wcsc_get_parceltemplate( $template_id )
{
	$parcels = wcsc_get_parceltemplates( array( 'include' => $template_id ) );
	$parcel  = $parcels[ 0 ];

	return $parcel;
}

/**
 * Gets Parcel Templates
 *
 * @param array $args
 *
 * @return array $parcel_templates Parcel Templates in an Array
 *
 * @since   1.0.0
 */
function wcsc_get_parceltemplates( $args = array() )
{
	$defaults = array(
		'posts_per_page' => - 1,
		'orderby'        => '',
		'order'          => '',
		'include'        => '',
		'exclude'        => ''
	);

	$args                = wp_parse_args( $args, $defaults );
	$args[ 'post_type' ] = 'sc_parcel_template';

	$posts            = get_posts( $args );
	$parcel_templates = array();

	foreach ( $posts AS $key => $post )
	{
		$parcel_templates[ $key ]                          = (array) $post;
		$parcel_templates[ $key ][ 'values' ][ 'carrier' ] = get_post_meta( $post->ID, 'carrier', true );
		$parcel_templates[ $key ][ 'values' ][ 'width' ]   = get_post_meta( $post->ID, 'width', true );
		$parcel_templates[ $key ][ 'values' ][ 'height' ]  = get_post_meta( $post->ID, 'height', true );
		$parcel_templates[ $key ][ 'values' ][ 'length' ]  = get_post_meta( $post->ID, 'length', true );
		$parcel_templates[ $key ][ 'values' ][ 'weight' ]  = get_post_meta( $post->ID, 'weight', true );
    $parcel_templates[ $key ][ 'values' ][ 'shipcloud_is_standard_parcel_template' ]  = get_post_meta( $post->ID, 'shipcloud_is_standard_parcel_template', true );
  }

	return $parcel_templates;
}

/**
 * Getting shipments of an order
 *
 * @param int $order_id
 *
 * @return mixed
 *
 * @since   1.1.0
 */
function wcsc_get_shipments( $order_id ) {
	return get_post_meta( $order_id, 'shipcloud_shipment_data' );
}


/**
 * Getting tracking numbers of an order
 *
 * @param int $order_id
 *
 * @return mixed
 *
 * @since   1.1.0
 */
function wcsc_get_tracking_numbers( $order_id ) {
	$tracking_numbers = array();
	$shipments = wcsc_get_shipments( $order_id );

	if( empty( $shipments ) ) {
		return $tracking_numbers;
	}

	foreach( $shipments AS $shipment ) {
		if( array_key_exists( 'carrier_tracking_no', $shipment ) && ! empty( $shipment[ 'carrier_tracking_no' ] ) ) {
			$tracking_numbers[] = $shipment[ 'carrier_tracking_no' ];
		}
	}

	return $tracking_numbers;
}
