<?php
/**
 * Validation Data
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Model_ValidationData
 */
class MT_Model_ValidationData {
	/**
	 * The value
	 *
	 * @var mixed
	 */
	private $value;
	/**
	 * The model
	 *
	 * @var MT_Interfaces_Model
	 */
	private $model;
	/**
	 * The field
	 *
	 * @var MT_Model_Field_Declaration
	 */
	private $field;

	/**
	 * Mixtape_Model_ValidationData constructor.
	 *
	 * @param mixed                           $value The value.
	 * @param MT_Interfaces_Model        $model The Model.
	 * @param MT_Model_Field_Declaration $field The Field.
	 */
	public function __construct( $value, $model, $field ) {
		$this->value = $value;
		$this->model = $model;
		$this->field = $field;
	}


	/**
	 * Get Value
	 *
	 * @return mixed $this->value the value that needs validation
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Get Model
	 *
	 * @return MT_Interfaces_Model
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * Get Field
	 *
	 * @return MT_Model_Field_Declaration
	 */
	public function get_field() {
		return $this->field;
	}
}
