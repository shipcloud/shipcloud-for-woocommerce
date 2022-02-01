<?php

/**
 * WC_Shipping_Shipcloud_Cpt_Parcel_Template represents a shipcloud parcel template.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Cpt_Parcel_Template' ) ) {
	
	class WC_Shipping_Shipcloud_Cpt_Parcel_Template {
		
		/**
		 * The Single instance of the class
		 *
		 * @var object $_instance
		 */
		protected static $_instance = null;
		
		/**
		 * @var object $api
		 */
		private $api;

		/**
		 * Construct
		 * 
		 * @return void
		 */
		private function __construct() {
			$this->init();
		}

		/**
		 * Initializing Post type
		 * 
		 * @return void
		 */
		private function init() {
			
			$this->api = WC_Shipping_Shipcloud_API_Adapter::get_instance();
			
			add_action( 'init', 					array( $this, 'register_post_type' ) );
			add_action( 'admin_menu', 				array( $this, 'add_menu_item' ), 50 );
			add_action( 'admin_enqueue_scripts', 	array( $this, 'admin_enqueue_scripts' ), 11 );
			add_action( 'add_meta_boxes', 			array( $this, 'meta_boxes' ), 10 );
			add_action( 'save_post', 				array( $this, 'save_post' ) );

			add_action( 'admin_notices', 			array( $this, 'notice_area' ) );

			add_filter( 'post_updated_messages', 	array( $this, 'remove_all_messages' ) );
		}

		/**
		 * Main Instance
		 *
		 * @return object
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Registering Post type
		 *
		 * @return void
		 */
		public function register_post_type() {
			$labels = array(
				'name'               => _x( 'Parcel templates', 'post type general name', 'shipcloud-for-woocommerce' ),
				'singular_name'      => _x( 'Parcel template', 'post type singular name', 'shipcloud-for-woocommerce' ),
				'menu_name'          => _x( 'Parcel templates', 'admin menu', 'shipcloud-for-woocommerce' ),
				'name_admin_bar'     => _x( 'Parcel template', 'add new on admin bar', 'shipcloud-for-woocommerce' ),
				'add_new'            => __( 'Add new', 'shipcloud-for-woocommerce' ),
				'add_new_item'       => __( 'Add new parcel template', 'shipcloud-for-woocommerce' ),
				'new_item'           => __( 'New parcel template', 'shipcloud-for-woocommerce' ),
				'edit_item'          => __( 'Edit parcel template', 'shipcloud-for-woocommerce' ),
				'view_item'          => __( 'View parcel template', 'shipcloud-for-woocommerce' ),
				'all_items'          => __( 'All parcel templates', 'shipcloud-for-woocommerce' ),
				'search_items'       => __( 'Search parcel templates', 'shipcloud-for-woocommerce' ),
				'parent_item_colon'  => __( 'Parent parcel templates:', 'shipcloud-for-woocommerce' ),
				'not_found'          => __( 'No parcel template found.', 'shipcloud-for-woocommerce' ),
				'not_found_in_trash' => __( 'No parcel templates found in trash.', 'shipcloud-for-woocommerce' )
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description', 'shipcloud-for-woocommerce' ),
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

			register_post_type( WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE, $args );
		}

		/**
	     * Builds link where a new template can be created.
	     *
		 * @return string
		 */
		public function get_create_link() {
			return get_admin_url( null, 'post-new.php?post_type=' . WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE );
		}

		/**
		 * Adding Parcels to Woo Menu
		 *
		 * @return void
		 */
		public function add_menu_item() {
			add_submenu_page( 
				'edit.php?post_type=product', // WC_SHIPPING_SHIPCLOUD_OPTIONS_NAME,
				__( 'Parcel templates', 'shipcloud-for-woocommerce' ), 
				__( 'Parcel templates', 'shipcloud-for-woocommerce' ), 
				'manage_options', 
				'edit.php?post_type=' . WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE 
			);
		}
		
		/**
		 * Enqueues admin related scripts and styles.
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			
			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
            
			if ( in_array( $screen_id, [ 'edit-sc_parcel_template', 'sc_parcel_template' ] ) ) {
                
			    include_once( dirname( __FILE__ ) . '/data/data-shipcloud-multiselect-script.php' );
			}
			
		}

		/**
		 * Adding Metaboxes
		 *
		 * @return void
		 */
		public function meta_boxes() {
			add_meta_box( 
				'box-tools', 
				__( 'Settings', 'shipcloud-for-woocommerce' ), 
				array( $this, 'box_settings' ), 
				WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE, 
				'normal' 
			);
		}
		
		/**
		 * Settings Box
		 *
		 * @return string
		 */
		public function box_settings() {
			global $post;

			if ( WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE !== $post->post_type ) {
				return;
			}

			$shipcloud_carriers = $this->api->get_carrier_list();

			$carriers = [];
			foreach( $shipcloud_carriers AS $carrier ) {
				$carriers[ $carrier['name'] ] = $carrier['display_name'];
			}
		
			$selected_carrier = get_post_meta( $post->ID, 'carrier', true );

			/* for compatibility reasons */
			if ( $selected_carrier !== '' && ! is_array( $selected_carrier ) ) {
				$tmp = explode( '_', $selected_carrier, 2 );
				$selected_carrier = array(
					'carrier' => $tmp[0],
					'service' => $tmp[1],
					'package' => null,
				);
			}

			$width	= get_post_meta( $post->ID, 'width', true );
			$height	= get_post_meta( $post->ID, 'height', true );
			$length	= get_post_meta( $post->ID, 'length', true );
			$weight	= get_post_meta( $post->ID, 'weight', true );
			
			$shipcloud_is_standard_parcel_template = get_post_meta( $post->ID, 'shipcloud_is_standard_parcel_template', true );

	  	  	include( dirname( __FILE__ ) . '/templates/template-meta-box-settings.php' );
		}
		
		/**
		 * Saving data
		 *
		 * @param int $post_id
		 * @return void
		 */
		public function save_post( $post_id ) {
			global $wpdb;

			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			$request = array_intersect_key(
				$_POST,
				array(
					'post_type'                 			=> null,
					'width'                     			=> null,
					'height'                    			=> null,
					'length'                    			=> null,
					'weight'                    			=> null,
					'shipcloud_carrier'         			=> null,
					'shipcloud_carrier_service' 			=> null,
					'shipcloud_carrier_package' 			=> null,
					'shipcloud_is_standard_parcel_template' => null,
				)
			);

			if ( ! array_key_exists( 'post_type', $request ) ) {
				return;
			}

			if ( WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE !== $request['post_type'] ) {
				return;
			}

			if ( ! array_key_exists( 'shipcloud_carrier', $request ) ) {
				return;
			}

			$width  = $request['width'];
			$height = $request['height'];
			$length = $request['length'];
			$weight = $request['weight'];
			
			$shipcloud_is_standard_parcel_template = $request['shipcloud_is_standard_parcel_template'];

			$post_title = WC_Shipping_Shipcloud_Utils::get_carrier_display_name( $request['shipcloud_carrier'] )
						  . ' ' . WC_Shipping_Shipcloud_Utils::get_service_name( $request['shipcloud_carrier_service'] )
						  . ' (' . WC_Shipping_Shipcloud_Utils::get_package_label( $request['shipcloud_carrier_package'] ) . ')'
						  . ' - ' . $width
						  . ' x ' . $height
						  . ' x ' . $length
						  . ' ' . __( 'cm', 'shipcloud-for-woocommerce' )
						  . ' ' . $weight . __( 'kg', 'shipcloud-for-woocommerce' );

			$wpdb->update( $wpdb->posts, array( 'post_title' => $post_title ), [ 'ID' => $post_id ] );

			update_post_meta(
				$post_id,
				'carrier', array(
					'carrier' => $request['shipcloud_carrier'],
					'service' => $request['shipcloud_carrier_service'],
					'package' => $request['shipcloud_carrier_package'],
				)
			);

			update_post_meta( $post_id, 'width', wc_format_decimal( $width ) );
			update_post_meta( $post_id, 'height', wc_format_decimal( $height ) );
			update_post_meta( $post_id, 'length', wc_format_decimal( $length ) );
			update_post_meta( $post_id, 'weight', wc_format_decimal( $weight ) );

			if ( $shipcloud_is_standard_parcel_template ) {
				$parcel_templates = WC_Shipping_Shipcloud_Utils::get_parcel_templates();
				if ( count( $parcel_templates ) > 0 ) {
					foreach ( $parcel_templates AS $parcel_template ) {
						if ( $parcel_template ['values']['shipcloud_is_standard_parcel_template'] ) {
							update_post_meta( $parcel_template['ID'], 'shipcloud_is_standard_parcel_template', "" );
						}
					}
				}
				update_post_meta( $post_id, 'shipcloud_is_standard_parcel_template', $shipcloud_is_standard_parcel_template );
			} else {
				update_post_meta( $post_id, 'shipcloud_is_standard_parcel_template', "" );
			}
			
			wp_redirect( admin_url( 'edit.php?post_type=' . WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE, 'https' ) );
			exit;
		}

		/**
		 * Notice Area
		 *
		 * @return void
		 */
		public function notice_area() {
			echo '<div class="shipcloud-message updated" style="display: none;"><p class="info"></p></div>';
		}

		/**
		 * Removing all messages
		 *
		 * @param array $messages
		 * @return array
		 */
		public function remove_all_messages( $messages ) {
			global $post;

			if ( get_class( $post ) !== 'WP_Post' ) {
				return $messages;
			}

			if ( WC_SHIPPING_SHIPCLOUD_CPT_PARCEL_TEMPLATE == $post->post_type ) {
				return [];
			}
		}
		
        /**
         * Output an admin notice.
         *
         * @param string 	$message 		Debug message.
         * @param string 	$type    		Message type.
         * @param bool 		$dismissible    Message type.
         * @return void
         */
        private function add_admin_notice( $message, $type = 'info', $dismissible = true ) {
            WC_Shipping_Shipcloud_Utils::add_admin_notice( $message, $type, $dismissible );
        }

		/**
		 * Output a debug message.
		 *
		 * @param string 	$message 	Debug message.
		 * @param string 	$level   	Debug level.
         * @param mixed 	$context	The Debug context.
		 * @return void
		 */
        private function log( $message, $level = 'info', $context = [] ) {
            WC_Shipping_Shipcloud_Utils::log( $message, $level, $context );
        }
	}
	
	WC_Shipping_Shipcloud_Cpt_Parcel_Template::instance();
}
