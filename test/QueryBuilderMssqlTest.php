<?php

class QueryBuilderMssqlTest extends \PHPUnit\Framework\TestCase {

    public function setUp(): void {
        // Enable logging
        \Titi\ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockMsSqlPDO('sqlite::memory:');
        \Titi\ORM::set_db($db);
    }

    public function tearDown(): void {
        \Titi\ORM::reset_config();
        \Titi\ORM::reset_db();
    }

    public function testFindOne() {
        \Titi\ORM::for_table('widget')->find_one();
        $expected = 'SELECT TOP 1 * FROM "widget"';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLimit() {
        \Titi\ORM::for_table('widget')->limit(5)->find_many();
        $expected = 'SELECT TOP 5 * FROM "widget"';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }
    
}

