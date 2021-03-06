<?php
/**
 * Environment
 *
 * Contains rest bundle, type and model definitions
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Environment
 *
 * Our global Environment
 *
 * @package Mixtape
 */
class MT_Environment {

	/**
	 * This environment's registered rest bundles (versioned APIs)
	 *
	 * @var array
	 */
	protected $rest_api_bundles;

	/**
	 * This environment's model definitions
	 *
	 * @var array
	 */
	protected $model_definitions;

	/**
	 * Did this Environment start?
	 *
	 * @var bool
	 */
	private $started;

	/**
	 * Our Bootstrap
	 *
	 * @var MT_Bootstrap
	 */
	private $bootstrap;

	/**
	 * Our Type Registry
	 *
	 * @var MT_Type_Registry
	 */
	private $type_registry;

	/**
	 * Our Fluent Define
	 *
	 * @var MT_Fluent_Define
	 */
	private $definer;

	/**
	 * Our Fluent Get
	 *
	 * @var MT_Fluent_Get
	 */
	private $getter;

	/**
	 * Queues of pending builders
	 *
	 * @var array
	 */
	private $pending_definitions;

	/**
	 * Mixtape_Environment constructor.
	 *
	 * @param MT_Bootstrap $bootstrap The bootstrap.
	 */
	public function __construct( $bootstrap ) {
		$this->bootstrap = $bootstrap;
		$this->started = false;
		$this->rest_api_bundles = array();
		$this->model_definitions = array();
		$this->pending_definitions = array(
			'models' => array(),
			'bundles' => array(),
		);
		$this->type_registry = new MT_Type_Registry();
		$this->type_registry->initialize( $this );
		$this->definer = new MT_Fluent_Define( $this );
		$this->getter = new MT_Fluent_Get( $this );
	}

	/**
	 * Push a Builder to the Environment.
	 *
	 * All builders are evaluated lazily when needed
	 *
	 * @param string                     $where The queue to push the builder to.
	 * @param MT_Interfaces_Builder $builder The builder to push.
	 *
	 * @return MT_Environment $this
	 * @throws MT_Exception In case the $builder is not a Mixtape_Interfaces_Builder.
	 */
	public function push_builder( $where, $builder ) {
		MT_Expect::is_a( $builder, 'MT_Interfaces_Builder');
		$this->pending_definitions[ $where ][] = $builder;
		return $this;
	}

	/**
	 * Retrieve a previously defined Mixtape_Model_Definition
	 *
	 * @param string $class the class name.
	 * @return MT_Model_Definition the definition.
	 * @throws MT_Exception Throws in case the model is not registered.
	 */
	public function model_definition( $class ) {
		if ( ! class_exists( $class ) ) {
			throw new MT_Exception( $class . ' does not exist' );
		}
		$this->load_pending_builders( 'models' );
		MT_Expect::that( isset( $this->model_definitions[ $class ] ), $class . ' definition does not exist' );
		return $this->model_definitions[ $class ];
	}

	/**
	 * Time to build pending models and bundles
	 *
	 * @param string $type One of (models, bundles).
	 */
	private function load_pending_builders( $type ) {
		foreach ( $this->pending_definitions[ $type ] as $pending ) {
			/**
			 * Our pending builder.
			 *
			 * @var MT_Interfaces_Builder $pending Our builder.
			 */
			if ( 'models' === $type ) {
				$this->add_model_definition( $pending->build() );
			}
			if ( 'bundles' === $type ) {
				$this->add_rest_bundle( $pending->build() );
			}
		}
	}

	/**
	 * Add a Bundle to our bundles (muse be Mixtape_Interfaces_Rest_Api_Controller_Bundle)
	 *
	 * @param MT_Interfaces_Controller_Bundle $bundle the bundle.
	 *
	 * @return MT_Environment $this
	 * @throws MT_Exception In case it's not a MT_Interfaces_Controller_Bundle.
	 */
	private function add_rest_bundle( $bundle ) {
		MT_Expect::is_a( $bundle, 'MT_Interfaces_Controller_Bundle' );
		$key = $bundle->get_bundle_prefix();
		$this->rest_api_bundles[ $key ] = $bundle;
		return $this;
	}

	/**
	 * Start things up
	 *
	 * This should be called once our Environment is set up to our liking.
	 * Evaluates all Builders, creating missing REST Api and Model Definitions.
	 * Hook this into rest_api_init
	 *
	 * @return MT_Environment $this
	 */
	public function start() {
		if ( false === $this->started ) {
			do_action( 'mixtape_environment_before_start', $this );
			$this->load_models();
			$this->load_pending_builders( 'bundles' );
			foreach ( $this->rest_api_bundles as $k => $bundle ) {
				$bundle->register();
			}
			$this->started = true;
			do_action( 'mixtape_environment_after_start', $this );
		}

		return $this;
	}

	/**
	 * Loads Models
	 *
	 * @return MT_Environment $this
	 */
	public function load_models() {
		$this->load_pending_builders( 'models' );
		return $this;
	}

	/**
	 * Auto start on rest_api_init. For more control, use ::start();
	 */
	public function auto_start() {
		add_action( 'rest_api_init', array( $this, 'start' ) );
	}

	/**
	 * Get this Environment's bootstrap instance
	 *
	 * @return MT_Bootstrap our bootstrap.
	 */
	public function get_bootstrap() {
		return $this->bootstrap;
	}

	/**
	 * Build a new Endpoint
	 *
	 * @param string $class the class to use.
	 *
	 * @return $this
	 * @throws MT_Exception In case our class is not compatible.
	 */
	public function endpoint( $class ) {
		$builder = new MT_Controller_Builder();
		return $builder->with_class( $class )->with_environment( $this );
	}

	/**
	 * Define something for this Environment
	 *
	 * @return MT_Fluent_Define
	 */
	public function define() {
		return $this->definer;
	}

	/**
	 * Get something from the Environment
	 *
	 * @return MT_Fluent_Get
	 */
	public function get() {
		return $this->getter;
	}

	/**
	 * Get our registered types
	 *
	 * @return MT_Type_Registry
	 */
	public function type() {
		return $this->type_registry;
	}

	/**
	 * Build a new Data Store
	 *
	 * @return MT_Data_Store_Builder
	 */
	public function data_store() {
		return new MT_Data_Store_Builder();
	}

	/**
	 * Add a new Definition into this Environment
	 *
	 * @param MT_Model_Definition $definition the definition to add.
	 * @return MT_Environment $this
	 */
	private function add_model_definition( $definition ) {
		$key = $definition->get_model_class();
		$this->model_definitions[ $key ] = $definition;
		return $this;
	}
}
