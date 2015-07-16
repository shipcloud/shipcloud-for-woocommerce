<?php

if ( !defined( 'ABSPATH' ) ) exit;

/*
* Getting Plugin Template
* @since 1.0.0
*/
if( defined( 'WCSC_FOLDER') ): // TODO: Replace PluginName
	function wcsc_locate_template( $template_names, $load = FALSE, $require_once = TRUE ) {
	    $located = '';
		
	    $located = locate_template( $template_names, $load, $require_once );
	
	    if ( '' == $located ):
			foreach ( ( array ) $template_names as $template_name ):
			    if ( !$template_name )
					continue;
			    if ( file_exists( WCSC_FOLDER . '/templates/' . $template_name ) ):
					$located = WCSC_FOLDER . '/templates/' . $template_name;
					break;
				endif;
			endforeach;
		endif;
	
	    if ( $load && '' != $located )
		    load_template( $located, $require_once );
	
	    return $located;
	}
endif;

/**
 * Debugging helper function
 */
if( !function_exists( 'p' ) ){
	function p( $var ){
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	}
}

/**
 * Deleting values
 */
function wcsc_delete_values(){
	global $wpdb;

	$post_type = 'shop_order';
	$meta_key = 'shipcloud_shipment_data';

	$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ", $post_type );
	$results = $wpdb->get_results( $sql );

	foreach( $results AS $result ){
		$post_id = $result->ID;
		delete_post_meta( $post_id, $meta_key );
	}
}
if( array_key_exists( 'wcscdeletevalues', $_GET ) )
	add_action( 'init', 'wcsc_delete_values' );