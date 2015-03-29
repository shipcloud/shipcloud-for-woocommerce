<?php
/*
 * ComponentName Core Class TODO
 *
 * This class initializes the component.
 *
 * @author awesome.ug <contact@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package PluginName/Admin
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

class WCSCAdmin extends WCSCComponent{
	/**
	 * Initializes the Component.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->name = __( 'Component Name', 'wcsc-locale' );
		$this->slug = 'component_name';
		
	    // Functions in Admin
	    if( is_admin() ):
		// Functions not in Admin
		else:
		endif;
			
		parent::__construct();
	} // end constructor
}
wcsc_load_component( 'ComponentNameAdmin' );
