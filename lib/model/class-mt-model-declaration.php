<?php
/**
 * Base Class for Creating Declarations
 *
 * @package Mixtape/Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Model_Declaration
 */
class MT_Model_Declaration implements MT_Interfaces_Model_Declaration {
	/**
	 * The Model Definition
	 *
	 * @var MT_Model_Definition
	 */
	private $model_definition;

	/**
	 * Set the definition
	 *
	 * @param MT_Model_Definition $def The def.
	 *
	 * @return MT_Interfaces_Model_Declaration $this
	 */
	function set_definition( $def ) {
		$this->model_definition = $def;
		return $this;
	}

	/**
	 * Get definition.
	 *
	 * @return MT_Model_Definition
	 */
	function definition() {
		return $this->model_definition;
	}

	/**
	 * Declare fields
	 *
	 * @param MT_Model_Field_Declaration_Collection_Builder $definition The def.
	 *
	 * @return void
	 * @throws MT_Exception Override this.
	 */
	function declare_fields( $definition ) {
		throw new MT_Exception( 'Override me: ' . __FUNCTION__ );
	}

	/**
	 * Get the id
	 *
	 * @param MT_Interfaces_Model $model The model.
	 *
	 * @return mixed|null
	 */
	function get_id( $model ) {
		return $model->get( 'id' );
	}

	/**
	 * Set the id
	 *
	 * @param MT_Interfaces_Model $model The model.
	 * @param mixed                    $new_id The new id.
	 *
	 * @return mixed|null
	 */
	function set_id( $model, $new_id ) {
		return $model->set( 'id', $new_id );
	}

	/**
	 * Call a method.
	 *
	 * @param string $method The method.
	 * @param array  $args The args.
	 *
	 * @return mixed
	 * @throws MT_Exception Throw if method nonexistent.
	 */
	function call( $method, $args = array() ) {
		if ( is_callable( $method ) ) {
			return $this->perform_call( $method, $args );
		}
		MT_Expect::that( method_exists( $this, $method ), $method . ' does not exist' );
		return $this->perform_call( array( $this, $method ), $args );
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	function get_name() {
		return strtolower( get_class( $this ) );
	}

	/**
	 * Perform call
	 *
	 * @param mixed $callable A Callable.
	 * @param array $args The args.
	 *
	 * @return mixed
	 */
	private function perform_call( $callable, $args ) {
		return call_user_func_array( $callable, $args );
	}
}
