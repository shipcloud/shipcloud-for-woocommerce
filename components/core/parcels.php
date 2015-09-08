<?php
/**
 * WooCommerce shipcloud.io pacel class
 *
 * Loading parcel functions
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud/Core
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 (very@awesome.ug)

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

class WCSC_Parcel_PostType{
    /**
     * Initializing Post type
     */
    public static function init(){
        add_action( 'init', array( __CLASS__, 'register_post_types' ) );
        add_action( 'admin_menu',  array( __CLASS__, 'add_menu' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ), 10 );
        add_action( 'edit_form_after_title', array( __CLASS__, 'box_settings' ) );
        add_action( 'save_post', array( __CLASS__, 'save' ) );

        add_action( 'admin_notices', array( __CLASS__, 'notice_area' ) );

        add_filter( 'post_updated_messages', array( __CLASS__, 'remove_all_messages' ) );
    }

    /**
     * Registering Post type
     */
    public static function register_post_types(){
        $labels = array(
            'name'               => _x( 'Parcels', 'post type general name', 'wcsc-locale' ),
            'singular_name'      => _x( 'Parcel', 'post type singular name', 'wcsc-locale' ),
            'menu_name'          => _x( 'Parcels', 'admin menu', 'wcsc-locale' ),
            'name_admin_bar'     => _x( 'Parcel', 'add new on admin bar', 'wcsc-locale' ),
            'add_new'            => _x( 'Add New', 'parcel', 'wcsc-locale' ),
            'add_new_item'       => __( 'Add New Parcel', 'wcsc-locale' ),
            'new_item'           => __( 'New Parcel', 'wcsc-locale' ),
            'edit_item'          => __( 'Edit Parcel', 'wcsc-locale' ),
            'view_item'          => __( 'View Parcel', 'wcsc-locale' ),
            'all_items'          => __( 'All Parcels', 'wcsc-locale' ),
            'search_items'       => __( 'Search Parcels', 'wcsc-locale' ),
            'parent_item_colon'  => __( 'Parent Parcels:', 'wcsc-locale' ),
            'not_found'          => __( 'No Parcel found.', 'wcsc-locale' ),
            'not_found_in_trash' => __( 'No Parcels found in Trash.', 'wcsc-locale' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Description', 'wcsc-locale' ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=shop_order',
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => false
        );

        register_post_type( 'sc_parcel_template', $args );
    }

    /**
     * Adding Parcels to Woo Menu
     */
    public static function add_menu(){
        add_submenu_page( 'edit.php?post_type=product', __( 'shipcloud Parcels', 'wcsc-locale' ), __( 'shipcloud Parcels', 'wcsc-locale' ), 'manage_options', 'edit.php?post_type=sc_parcel_template' );
    }

    /**
     * Adding Metaboxes
     */
    public static function meta_boxes(){
        add_meta_box(
            'box-tools',
            __( 'Tools', 'wcsc-locale' ),
            array( __CLASS__, 'box_tools' ),
            'sc_parcel_template',
            'side'
        );
    }

    public static function box_settings(){
        global $post;

        if ( 'sc_parcel_template' != $post->post_type ) {
            return;
        }

        $options = get_option( 'woocommerce_shipcloud_settings' );
        $shipcloud_api = new Woocommerce_Shipcloud_API( $options[ 'api_key' ] );

        $carriers = $shipcloud_api->get_carriers( TRUE );

        $selected_carrier = get_post_meta( $post->ID, 'carrier', TRUE );
        $width   = get_post_meta( $post->ID, 'width', TRUE );
        $height  = get_post_meta( $post->ID, 'height', TRUE );
        $length  = get_post_meta( $post->ID, 'length', TRUE );
        $weight  = get_post_meta( $post->ID, 'weight', TRUE );
        $retail_price  = get_post_meta( $post->ID, 'retail_price', TRUE );

        ?>
        <div id="wcsc-parcel-settings">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="carrier"><?php _e( 'Shipping Company', 'wcsc-locale' ); ?></label></th>
                        <td>
                            <select name="carrier">
                                <?php foreach( $carriers AS $carrier ): ?>
                                    <?php if( $selected_carrier == $carrier[ 'name' ] ): $selected = ' selected="selected"'; else: $selected = ''; endif; ?>
                                    <option value="<?php echo $carrier[ 'name' ]; ?>"<?php echo $selected; ?>><?php echo $carrier[ 'display_name' ]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="test"><?php _e( 'Width', 'wcsc-locale' ); ?></label></th>
                        <td><input type="text" name="width" value="<?php echo $width; ?>" /> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                    </tr>
                    <tr>
                        <th><label for="test"><?php _e( 'Height', 'wcsc-locale' ); ?></label></th>
                        <td><input type="text" name="height" value="<?php echo $height; ?>" /> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                    </tr>
                    <tr>
                        <th><label for="test"><?php _e( 'Length', 'wcsc-locale' ); ?></label></th>
                        <td><input type="text" name="length" value="<?php echo $length; ?>" /> <?php _e( 'cm', 'wcsc-locale' ); ?></td>
                    </tr>
                    <tr>
                        <th><label for="test"><?php _e( 'Weight', 'wcsc-locale' ); ?></label></th>
                        <td><input type="text" name="weight" value="<?php echo $weight; ?>" /> <?php _e( 'kg', 'wcsc-locale' ); ?></td>
                    </tr>
                    <tr>
                        <th><label for="test"><?php _e( 'Retail price', 'wcsc-locale' ); ?></label></th>
                        <td><input type="text" name="retail_price" value="<?php echo $retail_price; ?>" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function box_tools(){
        ?>
        <input type="button" id="check_parcel_settings" class="button" value="<?php _e( 'Check Parcel Settings', 'wcsc-locale' ); ?>" />
        <?php
    }

    /**
     * Saving data
     *
     * @param int $post_id
     * @since 1.0.0
     */
    public static function save( $post_id ){
        global $wpdb;

        if ( wp_is_post_revision( $post_id ) )
            return;

        if ( !array_key_exists( 'post_type', $_POST ) )
            return;

        if ( 'sc_parcel_template' != $_POST['post_type'] )
            return;

        if( !array_key_exists( 'carrier', $_POST ) )
            return;

        $carrier = $_POST[ 'carrier' ];
        $width = $_POST[ 'width' ];
        $height = $_POST[ 'height' ];
        $length = $_POST[ 'length' ];
        $weight = $_POST[ 'weight' ];
        $retail_price = str_replace( ',', '.', $_POST[ 'retail_price' ] );

        $post_title = wcsc_get_carrier_display_name( $carrier ) . ' - ' . $width . ' x ' . $height . ' x ' . $length . ' ' . __( 'cm', 'wcsc-locale' ) . ' ' . $weight . __( 'kg', 'wcsc-locale' );

        $where = array( 'ID' => $post_id );
        $wpdb->update( $wpdb->posts, array( 'post_title' => $post_title ), $where );

        update_post_meta( $post_id, 'carrier', $carrier );
        update_post_meta( $post_id, 'width', $width );
        update_post_meta( $post_id, 'height', $height );
        update_post_meta( $post_id, 'length', $length );
        update_post_meta( $post_id, 'weight', $weight );
        update_post_meta( $post_id, 'retail_price', $retail_price );
    }

    public static function notice_area(){
        echo '<div class="shipcloud-message updated" style="display: none;"><p class="info"></p></div>';
    }

    public static function remove_all_messages( $messages ){
        global $post;

        if( get_class( $post ) != 'WP_Post' )
            return $messages;

        if( 'sc_parcel_template' == $post->post_type )
            return array();

    }
}
WCSC_Parcel_PostType::init();

class WCSC_Parcels
{

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
            $parcel_templates[ $key ][ 'values'][ 'retail_price' ] = get_post_meta( $post->ID, 'retail_price', TRUE );
        }

        return $parcel_templates;
    }

    /**
     * Getting parcel data
     * @param $parcel_id
     */
    public static function get_parcel( $parcel_id ){
        $parcels = self::get( array( 'include'=> $parcel_id ) );
        $parcel = $parcels[ 0 ];

        return $parcel;
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
    public static function add( $carrier, $width, $height, $length, $weight, $retail_price = null ){
        $post = array(
            'post_title'    => $carrier . ' - ' . $width . 'x' . $height . 'x' . $length . __( 'cm', 'wcsc-locale' ) . ' ' . $weight . __( 'kg', 'wcsc-locale' ),
            'post_status'   => 'publish',
            'post_type'     => 'sc_parcel_template'
        );

        $post_id = wp_insert_post( $post );

        add_post_meta( $post_id, 'carrier', $carrier );
        add_post_meta( $post_id, 'width', $width );
        add_post_meta( $post_id, 'height', $height );
        add_post_meta( $post_id, 'length', $length );
        add_post_meta( $post_id, 'weight', $weight );
        add_post_meta( $post_id, 'retail_price', $retail_price );

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
                    <input type="text" name="parcel[customer_price]" value="<?php echo $parcel[ 'customer_price' ]; ?>" placeholder="" />
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
                        <td><?php echo $parcel_template[ 'customer_price' ]; ?></td>
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