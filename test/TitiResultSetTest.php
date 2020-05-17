<?php

use Titi\ORM;
use Titi\TitiResultSet;

class TitiResultSetTest extends \PHPUnit\Framework\TestCase {

    public function setUp(): void {
        // Enable logging
        \Titi\ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        \Titi\ORM::set_db($db);
    }

    public function tearDown(): void {
        \Titi\ORM::reset_config();
        \Titi\ORM::reset_db();
    }

    public function testGet() {
        $TitiResultSet = new TitiResultSet();
        $this->assertIsArray($TitiResultSet->get_results());
    }

    public function testConstructor() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet($result_set);
        $this->assertSame($TitiResultSet->get_results(), $result_set);
    }

    public function testSetResultsAndGetResults() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet();
        $TitiResultSet->set_results($result_set);
        $this->assertSame($TitiResultSet->get_results(), $result_set);
    }

    public function testAsArray() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet();
        $TitiResultSet->set_results($result_set);
        $this->assertSame($TitiResultSet->as_array(), $result_set);
    }

    public function testCount() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet($result_set);
        $this->assertSame($TitiResultSet->count(), 1);
        $this->assertSame(count($TitiResultSet), 1);
    }

    public function testGetIterator() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet($result_set);
        $this->assertInstanceOf('ArrayIterator', $TitiResultSet->getIterator());
    }

    public function testForeach() {
        $result_set = array('item' => new stdClass);
        $TitiResultSet = new TitiResultSet($result_set);
        $return_array = array();
        foreach($TitiResultSet as $key => $record) {
            $return_array[$key] = $record;
        }
        $this->assertSame($result_set, $return_array);
    }

    public function testCallingMethods() {
        $result_set = array('item' => \Titi\ORM::for_table('test'), 'item2' => \Titi\ORM::for_table('test'));
        $TitiResultSet = new TitiResultSet($result_set);
        $TitiResultSet->set('field', 'value')->set('field2', 'value');

        foreach($TitiResultSet as $record) {
            $this->assertTrue(isset($record->field));
            $this->assertSame($record->field, 'value');

            $this->assertTrue(isset($record->field2));
            $this->assertSame($record->field2, 'value');
        }
    }
    
}
