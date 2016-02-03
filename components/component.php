<?php
/**
 * Component Class
 *
 * Mother of all Components
 *
 * @author  awesome.ug <very@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (very@awesome.ug)
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
	exit;

abstract class WCSC_Component
{
	/**
	 * @var The Single instance of the class
	 */
	protected static $_instances = NULL;

	/**
	 * Name of Component
	 *
	 * @var $name
	 */
	var $name;

	/**
	 * Slug of Component
	 *
	 * @var $slug
	 */
	var $slug;

	/**
	 * Is Plugin active?
	 *
	 * @var $active
	 */
	var $active = TRUE;

	/**
	 * Main Instance
	 */
	public static function instance()
	{
		$class = get_called_class();

		if( !isset( self::$_instances[ $class ] ) )
		{
			self::$_instances[ $class ] = new $class();
			self::$_instances[ $class ]->init_base_hooks();

			add_action( 'plugins_loaded' , array( self::$_instances[ $class ], 'check_and_start' ), 30 );
		}

		return self::$_instances[ $class ];
	}

	/**
	 * Checking and starting
	 */
	public function check_and_start(){
		$class = get_called_class();

		if( TRUE == self::$_instances[ $class ]->check() )
		{
			self::$_instances[ $class ]->base_init();
		}
	}


	/**
	 * Function fot Checks
	 * @return mixed
	 */
	protected function check()
	{
		return TRUE;
	}

	/**
	 * Including needed Files.
	 *
	 * @since 1.0.0
	 */
	private function base_init()
	{
		if( method_exists( $this, 'init_hooks' ) )
		{
			$this->init_hooks();
		}

		if( method_exists( $this, 'includes' ) )
		{
			$this->includes();
		}

		if( method_exists( $this, 'scripts' ) )
		{
			$this->scripts();
		}

		if( method_exists( $this, 'init' ) )
		{
			$this->init();
		}
	}

	/**
	 * Initializing Base Hooks for all Components
	 */
	private function init_base_hooks()
	{
	}


	/**
	 * Adds a notice to
	 *
	 * @param        $message
	 * @param string $type
	 */
	protected function admin_notice( $message, $type = 'updated' )
	{
		if( WP_DEBUG )
		{
			$message = $message . ' (in Module "' .  $this->name . '")';
		}

		WooCommerce_Shipcloud::admin_notice( $message , $type );
	}
}

function wcsc_load_component( $component_name )
{
	if( class_exists( $component_name ) )
	{
		global $wcsc;
		$component = $component_name::instance();
		$wcsc[ 'components' ][ $component->slug ] = $component;
	}
}
