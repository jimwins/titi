<?php

use \Titi\ORM;
use \Titi\Model;

class ModelPrefixingTest extends \PHPUnit\Framework\TestCase {

    public function setUp(): void {
        // Set up the dummy database connection
        \Titi\ORM::set_db(new MockPDO('sqlite::memory:'));

        // Enable logging
        \Titi\ORM::configure('logging', true);
        
        Model::$auto_prefix_models = null;
    }

    public function tearDown(): void {
        \Titi\ORM::configure('logging', false);
        \Titi\ORM::set_db(null);

        Model::$auto_prefix_models = null;
    }

    public function testStaticPropertyExists() {
        $this->assertClassHasStaticAttribute('auto_prefix_models', '\Titi\Model');
        $this->assertNull(Model::$auto_prefix_models);
    }

    public function testSettingAndUnsettingStaticPropertyValue() {
        $model_prefix = 'My_Model_Prefix_';
        $this->assertNull(Model::$auto_prefix_models);
        Model::$auto_prefix_models = $model_prefix;
        $this->assertIsString(Model::$auto_prefix_models);
        $this->assertEquals($model_prefix, Model::$auto_prefix_models);
        Model::$auto_prefix_models = null;
        $this->assertNull(Model::$auto_prefix_models);
    }

    public function testNoPrefixOnAutoTableName() {
        Model::$auto_prefix_models = null;
        Model::factory('Simple')->find_many();
        $expected = 'SELECT * FROM `simple`';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testPrefixOnAutoTableName() {
        Model::$auto_prefix_models = 'MockPrefix_';
        Model::factory('Simple')->find_many();
        $expected = 'SELECT * FROM `mock_prefix_simple`';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testPrefixOnAutoTableNameWithTableSpecified() {
        Model::$auto_prefix_models = 'MockPrefix_';
        Model::factory('TableSpecified')->find_many();
        $expected = 'SELECT * FROM `simple`';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }
    
}
