<?php

/**
 * WC_Shipping_Shipcloud_Shipping_Classes represents several shipping classes.
 *
 * @category 	Class
 * @package 	WC_Shipping_Shipcloud
 * @author   	Daniel Muenter <info@msltns.com>
 * @license 	GPL 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WC_Shipping_Shipcloud_Shipping_Classes' ) ) {
	
	class WC_Shipping_Shipcloud_Shipping_Classes {

		/**
		 * The Single instance of the class
		 *
		 * @var object $_instance
		 */
		protected static $_instance = null;

		/**
		 * Construct
		 *
		 * @return void
		 */
		private function __construct() {
			$this->init();
		}

		/**
		 * Initializing functions
		 *
		 * @return void
		 */
		public function init() {
			add_action( 'admin_enqueue_scripts', 										array( $this, 'admin_enqueue_scripts_and_styles' ), 20 );
			add_filter( 'woocommerce_shipping_classes_columns', 						array( $this, 'add_shipping_class_columns' ) );
	        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-width',  	array( $this, 'add_shipping_class_col_width' ) );
	        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-height', 	array( $this, 'add_shipping_class_col_height' ) );
	        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-length', 	array( $this, 'add_shipping_class_col_length' ) );
	        add_action( 'woocommerce_shipping_classes_column_shipcloud-parcel-weight', 	array( $this, 'add_shipping_class_col_weight' ) );
			add_action( 'woocommerce_shipping_classes_save_class',						array( $this, 'save_shipping_class' ), 10, 2 );
			add_filter( 'woocommerce_get_shipping_classes',								array( $this, 'add_shipping_class_field_values' ), 10, 1 );
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
		 * Enqueues admin related scripts and styles.
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts_and_styles() {
			
			$screen       = get_current_screen();
			$screen_id    = $screen ? $screen->id : '';
						
			if ( $screen_id === 'woocommerce_page_wc-settings' ) {
				
				// Enqueue plugin scripts and styles.
				wp_enqueue_style( 'shipcloud-admin' );
				wp_enqueue_script( 'shipcloud-admin' );
			}
		}
	
	    /**
	     * Adding shipcloud columns to shipping classes.
	     *
	     * @param $columns
	     * @return array
	     */
		public function add_shipping_class_columns( $columns ) {
	        $shipcloud_columns = array(
	            'shipcloud-parcel-width'    => __( 'shipcloud parcel width (cm)', 'shipcloud-for-woocommerce' ),
	            'shipcloud-parcel-height'  	=> __( 'shipcloud parcel height (cm)', 'shipcloud-for-woocommerce' ),
	            'shipcloud-parcel-length'   => __( 'shipcloud parcel length (cm)', 'shipcloud-for-woocommerce' ),
	            'shipcloud-parcel-weight'   => __( 'shipcloud parcel weight (kg)', 'shipcloud-for-woocommerce' ),
	        );

	        $columns = array_merge( $columns, $shipcloud_columns );

	        return $columns;
	    }

	    /**
	     * Adding field for width.
	     *
		 * @return void
	     */
	    public function add_shipping_class_col_width() {
	        ?>
	        <div class="view">{{ data.width }}</div>
	        <div class="edit w100"><input type="text" name="width[{{ data.term_id }}]" data-attribute="width" value="{{ data.width }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
	        <?php
	    }

	    /**
	     * Adding field for height.
	     *
		 * @return void
	     */
	    public function add_shipping_class_col_height() {
	        ?>
	        <div class="view">{{ data.height }}</div>
	        <div class="edit w100"><input type="text" name="height[{{ data.term_id }}]" data-attribute="height" value="{{ data.height }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
	        <?php
	    }

	    /**
	     * Adding field for length.
	     *
		 * @return void
	     */
	    public function add_shipping_class_col_length() {
	        ?>
	        <div class="view">{{ data.length }}</div>
	        <div class="edit w100"><input type="text" name="length[{{ data.term_id }}]" data-attribute="length" value="{{ data.length }}" placeholder="<?php esc_attr_e( '0', 'woocommerce' ); ?>" /></div>
	        <?php
	    }

	    /**
	     * Adding field for weight (since WooCommerce 2.6)
	     *
		 * @return void
	     */
	    public function add_shipping_class_col_weight() {
	        ?>
	        <div class="view">{{ data.weight }}</div>
	        <div class="edit w100"><input type="text" name="weight[{{ data.term_id }}]" data-attribute="weight" value="{{ data.weight }}" placeholder="<?php esc_attr_e( '0.0', 'woocommerce' ); ?>" /></div>
	        <?php
	    }

		/**
		 * Saving class data (since WooCommerce 2.6)
		 *
		 * @param $term_id
		 * @param $data
		 * @return void
		 */
	    public function save_shipping_class( $term_id, $data ) {
			
		    if ( is_array( $term_id ) ) {
		    	$term_id = $term_id[ 'term_id' ];
		    }
		
			if ( isset( $data['width'] ) ) {
	            $parcel_width = wc_format_decimal( wc_clean( $data['width'] ) );
				update_option( 'shipping_class_' . $term_id . '_shipcloud_width', $parcel_width );
		    }

		    if ( isset( $data['height'] ) ) {
	            $parcel_height = wc_format_decimal( wc_clean( $data['height'] ) );
				update_option( 'shipping_class_' . $term_id . '_shipcloud_height', $parcel_height );
		    }

		    if ( isset( $data['length'] ) ) {
	            $parcel_length = wc_format_decimal( wc_clean( $data['length'] ) );
				update_option( 'shipping_class_' . $term_id . '_shipcloud_length', $parcel_length );
		    }

		    if ( isset( $data['weight'] ) ) {
	            $parcel_weight = wc_format_decimal( wc_clean( $data['weight'] ) );
				update_option( 'shipping_class_' . $term_id . '_shipcloud_weight', $parcel_weight );
		    }
			
	    }

	    /**
	     * Add shipping class field values (since WooCommerce 2.6)
	     *
	     * @param WP_Term[] $shipping_classes
	     * @return WP_Term[] $shipping_classes
	     */
	    public function add_shipping_class_field_values( $shipping_classes ) {
	        foreach( $shipping_classes AS $key => $shipping_class ) {
	            $term_id = $shipping_class->term_id;
	            $shipping_classes[ $key ]->width  =  get_option( 'shipping_class_' . $term_id . '_shipcloud_width' );
	            $shipping_classes[ $key ]->height =  get_option( 'shipping_class_' . $term_id . '_shipcloud_height' );
	            $shipping_classes[ $key ]->length =  get_option( 'shipping_class_' . $term_id . '_shipcloud_length' );
	            $shipping_classes[ $key ]->weight =  get_option( 'shipping_class_' . $term_id . '_shipcloud_weight' );
	        }

	        return $shipping_classes;
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

	WC_Shipping_Shipcloud_Shipping_Classes::instance();

}

