<?php

namespace Shipcloud;

/**
 * Simple service container.
 *
 * Acts more like a object pool but allows lazy instances.
 *
 * @package Shipcloud
 */
class ServiceContainer {
	private $raw = array();

	private $values = array();

	public function __construct( array $values = array() ) {
		$this->values = $values;
	}

	public function get( $id ) {
		if ( ! array_key_exists( $id, $this->values ) ) {
			throw new \DomainException( sprintf( 'Service %s does not exist.', $id ) );
		}

		if ( isset( $this->raw[ $id ] ) ) {
			return $this->raw[ $id ];
		}

		$value = $this->values[ $id ];

		if ( ! is_callable( $value ) ) {
			return $value;
		}

		return $this->raw[ $id ] = $value( $this );
	}

	public function set( $id, $value ) {
		$this->values[ $id ] = $value;
	}

	public function has( $id ) {
		return array_key_exists( $id, $this->values );
	}
}
