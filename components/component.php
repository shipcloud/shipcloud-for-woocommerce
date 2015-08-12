<?php
/**
 * Component Class
 *
 * Mother of all Components
 *
 * @author awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud
 * @version 1.0.0
 * @since 1.0.0
 * @license GPL 2

  Copyright 2015 awesome.ug (very@awesome.ug)

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

abstract class WCSCComponent{
    /**
     * Name of Component
     * @var $name
     */
	var $name;
    
    /**
     * Slug of Component
     * @var $slug
     */
    var $slug;
	
	/**
     * Is Plugin active?
     * @var $active
     */
    var $active = TRUE;
	
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
        $this->start();
	}
	
	/**
	 * Including needed Files.
	 * @since 1.0.0
	 */	
	public function start(){
		if( !$this->is_active() )
			return FALSE;

        $this->includes();
	}
	
	/**
	 * Checking if module is active
	 * @return boolean $is_active 
	 */
	public function is_active(){
		global $wcsc_passed_requirements;
		
		// Only start if there requirements passed
		if( !$wcsc_passed_requirements )
			return FALSE;
		
		return TRUE;
	}
}

function wcsc_load_component( $component_name ){
    if( class_exists( $component_name ) ):
        global $wcsc;
        $component = new $component_name();
        $wcsc[ 'components' ][ $component->slug ] = $component;
    endif;
}
