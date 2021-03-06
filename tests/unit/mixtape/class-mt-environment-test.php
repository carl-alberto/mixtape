<?php

class MT_EnvironmentTest extends MT_Testing_TestCase {
    /**
     * @var MT_Bootstrap
     */
    private $bootstrap;

    function setUp() {
        parent::setUp();
        $this->bootstrap = MT_Bootstrap::create()->load();
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Environment' );
        $env = $this->bootstrap->environment();
        $this->assertNotNull( $env );
        $this->assertInstanceOf( 'MT_Environment', $env );
    }

    /**
     * @covers Mixtape_Environment::start
     */
    function test_start_calls_register_in_added_bundles() {
        $a_bundle = $this
            ->getMockBuilder( MT_Interfaces_Controller_Bundle::class )
            ->setMethods( array( 'get_bundle_prefix', 'start', 'register', 'get_endpoints' ) )
            ->getMock();

        $a_bundle->expects($this->once())
            ->method('get_bundle_prefix')
            ->willReturn('/foo/v1');
        $a_bundle->expects($this->once())
            ->method('register');

        $this->bootstrap
            ->environment()
            ->define()->rest_api( $a_bundle );
        $this->bootstrap->environment()->start();
    }

    function test_endpoint_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint();
        $this->assertInstanceOf( 'MT_Controller_Builder', $b );
    }

    function test_crud_returns_builder() {
        $b = $this->bootstrap->environment()->define()->rest_api('zzz')->endpoint()->crud();
        $this->assertInstanceOf( 'MT_Controller_Builder', $b );
    }

    /**
     * @covers Mixtape_Environment::define
     */
    function test_define_return_fluid_define() {
        $define = $this->bootstrap->environment()->define();
        $this->assertInstanceOf( MT_Fluent_Define::class, $define );
    }

    /**
     * @covers Mixtape_Environment::get
     */
    function test_get_return_fluid_get() {
        $define = $this->bootstrap->environment()->get();
        $this->assertInstanceOf( MT_Fluent_Get::class, $define );
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::push_builder
     */
    function test_push_builder_throws_when_no_valid_class() {
        $this->bootstrap->environment()->push_builder( 'models', new stdClass());
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_unknown_class() {
        $this->bootstrap->environment()->model_definition( 'Foo' );
        ///
    }

    /**
     * @expectedException MT_Exception
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_throw_if_no_definition() {
        $this->bootstrap->environment()->model_definition( MT_Model_Declaration_Settings::class );
    }

    /**
     * @covers Mixtape_Environment::model_definition
     */
    function test_model_definition_return_definition() {
        $this->bootstrap->environment()->define()->model( MT_Model_Declaration_Settings::class );
        $d = $this->bootstrap->environment()->model_definition( MT_Model_Declaration_Settings::class );
        $this->assertInstanceOf( MT_Model_Definition::class, $d );
    }
}