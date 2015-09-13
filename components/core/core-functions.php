<?php
/**
 * WooCommerce shipcloud.io Core Functions
 *
 * Loading parcel functions
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 (very@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

function wcsc_add_parceltemplate(  $carrier, $width, $height, $length, $weight )
{
	$post = array(
		'post_title'  => $carrier . ' - ' . $width . 'x' . $height . 'x' . $length . __( 'cm', 'woocommerce-shipcloud' ) . ' ' . $weight . __( 'kg', 'woocommerce-shipcloud' ),
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

function wcsc_delete_parceltemplate( $post_id )
{
	add_action( 'wcsc_delete_parcel_template', $post_id );

	return wp_delete_post( $post_id );
}

function wcsc_get_parceltemplate( $parcel_id )
{
	$parcels = wcsc_get_parceltemplates( array( 'include' => $parcel_id ) );
	$parcel = $parcels[ 0 ];

	return $parcel;
}

function wcsc_get_parceltemplates( $args = array() )
{
	global $wpdb;

	$defaults = array(
		'posts_per_page' => -1,
		'orderby'        => '',
		'order'          => '',
		'include'        => '',
		'exclude'        => ''
	);

	$args = wp_parse_args( $args, $defaults );
	$args[ 'post_type' ] = 'sc_parcel_template';

	$posts = get_posts( $args );
	$parcel_templates = array();

	foreach( $posts AS $key => $post )
	{
		$parcel_templates[ $key ] = (array) $post;
		$parcel_templates[ $key ][ 'values' ][ 'carrier' ] = get_post_meta( $post->ID, 'carrier', TRUE );
		$parcel_templates[ $key ][ 'values' ][ 'width' ] = get_post_meta( $post->ID, 'width', TRUE );
		$parcel_templates[ $key ][ 'values' ][ 'height' ] = get_post_meta( $post->ID, 'height', TRUE );
		$parcel_templates[ $key ][ 'values' ][ 'length' ] = get_post_meta( $post->ID, 'length', TRUE );
		$parcel_templates[ $key ][ 'values' ][ 'weight' ] = get_post_meta( $post->ID, 'weight', TRUE );
	}

	return $parcel_templates;
}