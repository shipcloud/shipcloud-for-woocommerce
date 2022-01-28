<?php

namespace shipcloud\phpclient;

/**
 * Logger Class to log certain events.
 * 
 * @example
 * <pre>
 * use shipcloud\phpclient\Logger;
 * Logger::get_instance()->log( 'msg', 'info' );
 * </pre>
 *
 * @category 	Class
 * @package  	shipcloud\phpclient
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class Logger {
	
	/**
	 * Instance object of this class.
	 *
	 * @var Logger
	 */
	private static $instance;
	
	/**
	 * The actual logger object wrapped in this instance.
	 *
	 * @var \Monolog\Logger
	 */
	private static $logger;
	
	/**
	 * Logger constructor set private regarding singleton pattern usage.
	 * @return void
	 */
	private function __construct() {
		
	}
	
	/**
	 * Init method
	 * 
	 * @param string  $logfile
	 * @return void
	 */
	private static function init( string $name = '', string $logfile = '' ) {
		if ( empty( $logfile ) ) {
			$logfile = __DIR__ . "/../logs/{$name}.log";
		}
		
		$streamHandler 	= new \Monolog\Handler\StreamHandler( $logfile, \Monolog\Logger::DEBUG );
		$streamHandler->setFormatter( new \shipcloud\phpclient\SimpleFormatter() );
		
		self::$logger = new \Monolog\Logger( $name );
		self::$logger->pushHandler( $streamHandler );
	}
	
	/**
	 * Get an instance.
	 * 
	 * @param string  $logfile
	 * @return Logger
	 */
	public static function get_instance( string $name = '', string $logfile = '' ) {
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
			self::init( $name, $logfile );
		}
		return self::$instance;
	}
	
	/**
	 * Logs an info message. Same as Logger::log() giving
	 * second argument string 'info'.
	 * 
	 * @param mixed  $msg
	 * @return void
	 */
	public static function info( $msg ) {
		self::log( $msg, 'info' );
	}
	
	/**
	 * Logs an error message. Same as Logger::log() giving
	 * second argument string 'error'.
	 * 
	 * @param mixed  $msg
	 * @return void
	 */
	public static function error( $msg ) {
		self::log( $msg, 'error' );
	}
	
	/**
	 * Logs a warning message. Same as Logger::log() giving
	 * second argument string 'warning'.
	 * 
	 * @param mixed  $msg
	 * @return void
	 */
	public static function warn( $msg ) {
		self::log( $msg, 'warning' );
	}
	
	/**
	 * Logs a warning message. Same as Logger::log() giving
	 * second argument string 'warning'.
	 * 
	 * @param mixed  $msg
	 * @return void
	 */
	public static function warning( $msg ) {
		self::log( $msg, 'warning' );
	}
	
	/**
	 * Logs a message of given type and context. If no level is given
	 * it logs at info level.
	 * 
	 * @param mixed  	$msg
	 * @param string  	$level
	 * @param array 	$context
	 * @return void
	 */
	public static function log( $msg, string $level = 'info', array $context = [] ) {
		if ( ! isset( self::$logger ) ) {
			self::init();
		}
		if ( ! in_array( $level, [ 'info', 'warning', 'error' ] ) ) {
			$level = 'info';
		}
		if ( is_array( $msg ) || is_object( $msg ) ) {
            self::$logger->{$level}( print_r( $msg, true ), $context );
        } else {
            self::$logger->{$level}( $msg, $context );
        }
	}
}
