<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MT_Type_Nullable extends MT_Type {

	private $item_type_definition;

	/**
	 * @param MT_Interfaces_Type $item_type_definition
	 */
	function __construct( $item_type_definition ) {
		parent::__construct( 'nullable:' . $item_type_definition->name() );
		$this->item_type_definition = $item_type_definition;
	}

	public function default_value() {
		return null;
	}

	public function cast( $value ) {
		if ( null === $value ) {
			return null;
		}
		return $this->item_type_definition->cast( $value );
	}

	public function sanitize( $value ) {
		if ( null === $value ) {
			return null;
		}
		return $this->item_type_definition->sanitize( $value );
	}

	function schema() {
		$schema = parent::schema();
		$schema['type'] = array_unique( array_merge( $schema['type'], array( 'null' ) ) );
	}
}
