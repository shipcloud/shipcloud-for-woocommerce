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