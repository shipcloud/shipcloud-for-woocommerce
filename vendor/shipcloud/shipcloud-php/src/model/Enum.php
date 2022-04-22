<?php

namespace shipcloud\phpclient\model;

/**
 * Abstract enumeration class.
 * 
 * Implemented base for all enumeration types.
 *
 * @category 	Enum
 * @package  	shipcloud\phpclient\model
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
abstract class Enum {
	
	private static $constCacheArray = NULL;
	
	/**
     * Due to singleton pattern make sure there are never any instances created.
     *
     * @return void
     */
    final private function __construct() {
        throw new \Exception( 'Enum and Subclasses cannot be instantiated.' );
    }
	
    /**
     * Returns the name of the current instance.
     *
     * @return string
     */
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
	/**
     * Validates a given $enum_value against a given $enum_type.
     *
     * @param string $enum_type
     * @param string $enum_value
     * @return bool
     */
    public static function is_valid_value( $enum_type, $enum_value ) : bool {
		if ( class_exists( $enum_type ) ) {
            $reflector = new \ReflectionClass( $enum_type );
			if ( $reflector->IsSubClassOf( self::get_class_name() ) ) {
                foreach( $reflector->getConstants() as $label => $value ) {
                    if ( $value == $enum_value ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
	
	// CarrierType::get_name_by_value( CarrierType::get_class_name(), $carrier_name );
	public static function get_name_by_value( $enum_type, $enum_value ) {
		if ( class_exists( $enum_type ) ) {
            $reflector = new \ReflectionClass( $enum_type );
			if ( $reflector->IsSubClassOf( self::get_class_name() ) ) {
                foreach( $reflector->getConstants() as $name => $value ) {
                    if ( $value == $enum_value ) {
                        return $name;
                    }
                }
            }
        }
        return false;
	}
	
    /**
     * For a given $enum_type, give the complete string representation for the given $enum_value (class::const)
     *
     * @param string $enum_type
     * @param string $enum_value
     * @return string
     */
    public static function to_string( $enum_type, $enum_value ) : string {
		$result = 'NotAnEnum::IllegalValue';
        if ( class_exists( $enum_type, false ) ) {
            $reflector = new \ReflectionClass( $enum_type );
            $result = $reflector->getName() . '::IllegalValue';
            foreach( $reflector->getConstants() as $key => $val ) {
                if ( $val == $enum_value ) {
                    $result = str_replace( 'IllegalValue', $key, $result );
                    break;
                }
            }
        }
        return $result;
    }
}
