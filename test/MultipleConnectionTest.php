<?php

class MultipleConnectionTest extends \PHPUnit\Framework\TestCase {

    const ALTERNATE = 'alternate'; // Used as name of alternate connection

    public function setUp(): void {
        // Set up the dummy database connections
        \Titi\ORM::set_db(new MockPDO('sqlite::memory:'));
        \Titi\ORM::set_db(new MockDifferentPDO('sqlite::memory:'), self::ALTERNATE);

        // Enable logging
        \Titi\ORM::configure('logging', true);
        \Titi\ORM::configure('logging', true, self::ALTERNATE);
    }

    public function tearDown(): void {
        \Titi\ORM::reset_config();
        \Titi\ORM::reset_db();
    }

    public function testMultiplePdoConnections() {
        $this->assertInstanceOf('MockPDO', \Titi\ORM::get_db());
        $this->assertInstanceOf('MockPDO', \Titi\ORM::get_db(\Titi\ORM::DEFAULT_CONNECTION));
        $this->assertInstanceOf('MockDifferentPDO', \Titi\ORM::get_db(self::ALTERNATE));
    }

    public function testRawExecuteOverAlternateConnection() {
        $expected = "SELECT * FROM `foo`";
        \Titi\ORM::raw_execute("SELECT * FROM `foo`", array(), self::ALTERNATE);

        $this->assertEquals($expected, \Titi\ORM::get_last_query(self::ALTERNATE));
    }

    public function testFindOneOverDifferentConnections() {
        \Titi\ORM::for_table('widget')->find_one();
        $statementOne = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockPDOStatement', $statementOne);

        \Titi\ORM::for_table('person', self::ALTERNATE)->find_one();
        $statementOne = \Titi\ORM::get_last_statement(); // get_statement is *not* per connection
        $this->assertInstanceOf('MockDifferentPDOStatement', $statementOne);

        $expected = "SELECT * FROM `widget` LIMIT 1";
        $this->assertNotEquals($expected, \Titi\ORM::get_last_query()); // Because get_last_query() is across *all* connections
        $this->assertEquals($expected, \Titi\ORM::get_last_query(\Titi\ORM::DEFAULT_CONNECTION));

        $expectedToo = "SELECT * FROM `person` LIMIT 1";
        $this->assertEquals($expectedToo, \Titi\ORM::get_last_query(self::ALTERNATE));
    }

}