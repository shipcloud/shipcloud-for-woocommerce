<?php
/**
 * WooCommerce shipcloud.io pacel class
 *
 * Loading parcel functions
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Woo
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (contact@awesome.ug)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !defined( 'ABSPATH' ) ) exit;

class WCSCParcel_templates
{

    /**
     * Initializing Post type
     */
    public static function init(){
        add_action( 'init', array( __CLASS__, 'register_post_types' ) );
    }

    /**
     * Registering Post type
     */
    public static function register_post_types(){
        $args = array(
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null
        );

        register_post_type( 'sc_parcel_template', $args );
    }

    /**
     * Getting all templates
     * @param array $args
     */
    public static function get( $args = null ){
        global $wpdb;

        $defaults = array(
            'posts_per_page'    => -1,
            'orderby'           => '',
            'order'             => '',
            'include'           => '',
            'exclude'           => ''
        );

        $args = wp_parse_args( $args, $defaults );
        $args[ 'post_type' ] = 'sc_parcel_template';

        $posts = get_posts( $args );
        $parcel_templates = array();

        foreach( $posts AS $key => $post ){
            $parcel_templates[ $key ] = (array) $post;
            $parcel_templates[ $key ][ 'values'][ 'carrier' ] = get_post_meta( $post->ID, 'carrier', TRUE );
            $parcel_templates[ $key ][ 'values'][ 'width' ] = get_post_meta( $post->ID, 'width', TRUE );
            $parcel_templates[ $key ][ 'values'][ 'height' ] = get_post_meta( $post->ID, 'height', TRUE );
            $parcel_templates[ $key ][ 'values'][ 'length' ] = get_post_meta( $post->ID, 'length', TRUE );
            $parcel_templates[ $key ][ 'values'][ 'weight' ] = get_post_meta( $post->ID, 'weight', TRUE );
            $parcel_templates[ $key ][ 'values'][ 'customer_price' ] = get_post_meta( $post->ID, 'customer_price', TRUE );
        }

        return $parcel_templates;
    }

    /**
     * Adding a parcel template
     * @param $carrier
     * @param $width
     * @param $height
     * @param $length
     * @param $weight
     * @param null $customer_price
     * @return int|WP_Error
     */
    public static function add( $carrier, $width, $height, $length, $weight, $customer_price = null ){
        $post = array(
            'post_title'    => $carrier . ' - ' . $width . 'x' . $height . 'x' . $length . __( 'cm', 'wcsc-locale' ) . ' ' . $weight . __( 'kg', 'wcsc-locale' ),
            'post_status'   => 'publish',
            'post_type'     => 'sc_parcel_template'
        );

        $post_id = wp_insert_post( $post, $wp_error );

        add_post_meta( $post_id, 'carrier', $carrier );
        add_post_meta( $post_id, 'width', $width );
        add_post_meta( $post_id, 'height', $height );
        add_post_meta( $post_id, 'length', $length );
        add_post_meta( $post_id, 'weight', $weight );
        add_post_meta( $post_id, 'customer_price', $customer_price );

        add_action( 'wcsc_add_parcel_template', $post_id );

        return $post_id;
    }

    /**
     * Deleting a parcel template
     * @param $id
     */
    public static function delete( $post_id ){
        add_action( 'wcsc_delete_parcel_template', $post_id );

        return wp_delete_post( $post_id );
    }

    /**
     * List Parcel templates
     */
    public function show(){
        $options = get_option( 'woocommerce_shipcloud_settings' );
        $parcel_templates = get_option( 'woocommerce_shipcloud_parcel_templates', array() );

        $shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );
        $carriers = $shipcloud_api->get_carriers( TRUE );

        ob_start();

        ?>
        <table class="widefat" id="parcel_table">

            <thead>
            <tr>
                <th>
                    <label for="parcel[carrier]"><?php _e( 'Carrier', 'wcsc-locale' ); ?></label>
                </th>
                <th>
                    <label for="parcel[width]"><?php _e( 'Width', 'wcsc-locale' ); ?></label>
                </th>
                <th>
                    <label for="parcel[height]"><?php _e( 'Height', 'wcsc-locale' ); ?></label>
                </th>
                <th>
                    <label for="parcel[length]"><?php _e( 'Length', 'wcsc-locale' ); ?></label>
                </th>
                <th>
                    <label for="parcel[weight]"><?php _e( 'Weight', 'wcsc-locale' ); ?></label>
                </th>
                <th>
                    <label for="parcel[weight]"><?php _e( 'Customer Price', 'wcsc-locale' ); ?></label>
                </th>
                <th>

                </th>
            </tr>
            </thead>

            <tbody>
            <tr id="parcel_options">
                <td class="parcel_option carrier">
                    <select name="parcel[carrier]">
                        <?php foreach( $carriers AS $carrier ): ?>
                            <?php if( $parcel['carrier'] == $carrier[ 'name' ] ): $selected = ' selected="selected"'; else: $selected = ''; endif; ?>
                            <option value="<?php echo $carrier[ 'name' ]; ?>"<?php echo $selected; ?>><?php echo $carrier[ 'display_name' ]; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="parcel_option parcel_width">
                    <input type="text" name="parcel[width]" value="<?php echo $parcel[ 'width' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
                </td>
                <td class="parcel_option parcel_height">
                    <input type="text" name="parcel[height]" value="<?php echo $parcel[ 'height' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
                </td>
                <td class="parcel_option parcel_length">
                    <input type="text" name="parcel[length]" value="<?php echo $parcel[ 'length' ]; ?>" placeholder="<?php _e( 'cm', 'wcsc-locale'  ); ?>" />
                </td>
                <td class="parcel_option parcel_weight">
                    <input type="text" name="parcel[weight]" value="<?php echo $parcel[ 'weight' ]; ?>" placeholder="<?php _e( 'kg', 'wcsc-locale'  ); ?>" />
                </td>
                <td class="parcel_option parcel_weight">
                    <input type="text" name="parcel[customer_price]" value="<?php echo $parcel[ 'customer_price' ]; ?>" placeholder="<?php _e( 'EUR?', 'wcsc-locale'  ); ?>" />
                </td>
                <td class="parcel_option parcel_button">
                    <input type="button" id="shipcloud_verify_parcel_settings" value="<?php _e( 'Verify Parcel Settings', 'wcsc-locale'  ); ?>" class="button" />
                    <input type="button" id="shipcloud_add_parcel_template" value="<?php _e( 'Save as draft', 'wcsc-locale'  ); ?>" class="button" />
                </td>
            </tr>

            <?php if( '' != $parcel_templates && is_array( $parcel_templates ) ): ?>
                <?php $i = 0; ?>
                <?php foreach( $parcel_templates AS $parcel_template ): ?>
                    <tr<?php echo $i % 2 == 0 ? ' class="alt"': ''; ?>>
                        <td><?php // echo self::get_carrier_display_name( $parcel_template[ 'carrier' ] ); ?></td>
                        <td><?php echo $parcel_template[ 'width' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                        <td><?php echo $parcel_template[ 'height' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                        <td><?php echo $parcel_template[ 'length' ]; ?> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                        <td><?php echo $parcel_template[ 'weight' ]; ?> <?php _e( 'kg', 'wcsc-locale' ); ?></td>
                        <td><?php echo $parcel_template[ 'customer_price' ]; ?> <?php _e( 'EUR?', 'wcsc-locale' ); ?></td>
                        <td>
                            <input type="button" class="carrier_delete button"  value="<?php _e( 'Delete', 'wcsc-locale'  ); ?>" />
                            <input type="button" class="carrier_select button" value="<?php _e( 'Select', 'wcsc-locale'  ); ?>" />
                            <input type="hidden" name="carrier" value="<?php echo $parcel_template[ 'carrier' ]; ?>">
                            <input type="hidden" name="width" value="<?php echo $parcel_template[ 'width' ]; ?>" />
                            <input type="hidden" name="height" value="<?php echo $parcel_template[ 'height' ]; ?>" />
                            <input type="hidden" name="length" value="<?php echo $parcel_template[ 'length' ]; ?>" />
                            <input type="hidden" name="weight" value="<?php echo $parcel_template[ 'weight' ]; ?>" />
                            <input type="hidden" name="customer_price" value="<?php echo $parcel_template[ 'customer_price' ]; ?>" />
                        </td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php

        return ob_get_clean();
    }

    public function save_templates_form_data(){

    }
}
WCSCParcel_templates::init();

// Testing

// WCSCParcel_templates::add( 'ups', 50, 50, 50, 10, 5.4 );
// WCSCParcel_templates::delete( 73 );
// p( WCSCParcel_templates::get() );