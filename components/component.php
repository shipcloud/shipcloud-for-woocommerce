<?php
/**
 * Component Class
 * Mother of all WCSC Components
 *
 * @author  awesome.ug <support@awesome.ug>, Sven Wagener <sven@awesome.ug>
 * @package WooCommerceShipCloud
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2015 awesome.ug (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

abstract class WCSC_Component
{
	/**
	 * The Single instance of the class
	 *
	 * @var object $_instances
	 * @since 1.0.0
	 */
	protected static $_instances = null;

	/**
	 * Name of Component
	 *
	 * @var string $name
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Slug of Component
	 *
	 * @var string $slug
	 * @since 1.0.0
	 */
	var $slug;

	/**
	 * Is Plugin active?
	 *
	 * @var boolean $active
	 * @since 1.0.0
	 */
	var $active = true;

	/**
	 * Main Instance
	 *
	 * @since 1.0.0
	 */
	public static function instance()
	{
		$class = get_called_class();

		if ( ! isset( self::$_instances[ $class ] ) )
		{
			self::$_instances[ $class ] = new $class();
			self::$_instances[ $class ]->init_base_hooks();

			add_action( 'plugins_loaded', array( self::$_instances[ $class ], 'check_and_start' ), 30 );
		}

		return self::$_instances[ $class ];
	}

	/**
	 * Checking and starting
	 *
	 * @since 1.0.0
	 */
	public function check_and_start()
	{
		$class = get_called_class();

		if ( true == self::$_instances[ $class ]->check() )
		{
			self::$_instances[ $class ]->base_init();
		}
	}

	/**
	 * Function fot Checks
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function check()
	{
		return true;
	}

	/**
	 * Adds a notice to
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @since 1.0.0
	 */
	protected function admin_notice( $message, $type = 'updated' )
	{
		if ( WP_DEBUG )
		{
			$message = $message . ' (in Module "' . $this->name . '")';
		}

		WooCommerce_Shipcloud::admin_notice( $message, $type );
	}

	/**
	 * Including needed Files.
	 *
	 * @since 1.0.0
	 */
	private function base_init()
	{
		if ( method_exists( $this, 'init_hooks' ) )
		{
			$this->init_hooks();
		}

		if ( method_exists( $this, 'includes' ) )
		{
			$this->includes();
		}

		if ( method_exists( $this, 'scripts' ) )
		{
			$this->scripts();
		}

		if ( method_exists( $this, 'init' ) )
		{
			$this->init();
		}
	}

	/**
	 * Initializing Base Hooks for all Components
	 *
	 * @since 1.0.0
	 */
	private function init_base_hooks()
	{
	}
}

/**
 * Function to load WooCommerce Shipcloud components
 *
 * @since 1.0.0
 */
function wcsc_load_component( $component_name )
{
	if ( class_exists( $component_name ) )
	{
		global $wcsc;
		$component                                = $component_name::instance();
		$wcsc[ 'components' ][ $component->slug ] = $component;
	}
}
