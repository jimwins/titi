<?php

use Titi\ORM;
use Titi\Model;

class MultipleConnectionsTest extends \PHPUnit\Framework\TestCase {

    const ALTERNATE = 'alternate';

    public function setUp(): void {

        // Set up the dummy database connection
        \Titi\ORM::set_db(new MockPDO('sqlite::memory:'));
        \Titi\ORM::set_db(new MockDifferentPDO('sqlite::memory:'), self::ALTERNATE);

        // Enable logging
        \Titi\ORM::configure('logging', true);
        \Titi\ORM::configure('logging', true, self::ALTERNATE);
    }

    public function tearDown(): void {
        \Titi\ORM::configure('logging', false);
        \Titi\ORM::configure('logging', false, self::ALTERNATE);

        \Titi\ORM::set_db(null);
        \Titi\ORM::set_db(null, self::ALTERNATE);
    }

    public function testMultipleConnections() {
        $simple = Model::factory('Simple')->find_one(1);
        $statement = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockPDOStatement', $statement);

        $simple = Model::factory('Simple', self::ALTERNATE); // Change the object's default connection
        $simple->find_one(1);
        $statement = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockDifferentPDOStatement', $statement);

        $temp = Model::factory('Simple', self::ALTERNATE)->find_one(1);
        $statement = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockDifferentPDOStatement', $statement);
    }

    public function testCustomConnectionName() {
        $person3 = Model::factory('ModelWithCustomConnection')->find_one(1);
        $statement = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockDifferentPDOStatement', $statement);
    }

}
