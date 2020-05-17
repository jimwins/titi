<?php

class CacheTest extends \PHPUnit\Framework\TestCase {

    const ALTERNATE = 'alternate'; // Used as name of alternate connection

    public function setUp(): void {
        // Set up the dummy database connections
        \Titi\ORM::set_db(new MockPDO('sqlite::memory:'));
        \Titi\ORM::set_db(new MockDifferentPDO('sqlite::memory:'), self::ALTERNATE);

        // Enable logging
        \Titi\ORM::configure('logging', true);
        \Titi\ORM::configure('logging', true, self::ALTERNATE);
        \Titi\ORM::configure('caching', true);
        \Titi\ORM::configure('caching', true, self::ALTERNATE);
    }

    public function tearDown(): void {
        \Titi\ORM::reset_config();
        \Titi\ORM::reset_db();
    }

    // Test caching. This is a bit of a hack.
    public function testQueryGenerationOnlyOccursOnce() {
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
        \Titi\ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
        $expected = \Titi\ORM::get_last_query();
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this shouldn't run a query!
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testQueryGenerationOnlyOccursOnceWithMultipleConnections() {
        // Test caching with multiple connections (also a bit of a hack)
        \Titi\ORM::for_table('widget', self::ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one();
        \Titi\ORM::for_table('widget', self::ALTERNATE)->where('name', 'Tom')->where('age', 120)->find_one();
        $expected = \Titi\ORM::get_last_query();
        \Titi\ORM::for_table('widget', self::ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one(); // this shouldn't run a query!
        $this->assertEquals($expected, \Titi\ORM::get_last_query(self::ALTERNATE));
    }
    
}