<?php

class CacheIntegrationTest extends \PHPUnit\Framework\TestCase {

    public function setUp(): void {
        \Titi\ORM::configure('sqlite::memory:');
        \Titi\ORM::configure('logging', true);
        \Titi\ORM::configure('caching', true);

        \Titi\ORM::raw_execute('CREATE TABLE `league` ( `class_id` INTEGER )');
        // needs to be individually inserted to support SQLite before
        // version 3.7.11
        \Titi\ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (1)');
        \Titi\ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (2)');
        \Titi\ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (3)');

        $x = \Titi\ORM::for_table('league')->count();
        $this->assertEquals(3, $x);
    }

    public function tearDown(): void {
        \Titi\ORM::raw_execute('DROP TABLE `league`');
    }

    public function testRegressionForPullRequest319() {
        $rs = \Titi\ORM::for_table('league')->where('class_id', 1);
        $total = $rs->count();
        $this->assertEquals(1, $total);
        $row = $rs->find_one();
        $this->assertEquals(array('class_id' => 1), $row->as_array());

        $rs = \Titi\ORM::for_table('league')->where('class_id', 1);
        $total = $rs->count();
        $this->assertEquals(1, $total);
        try {
            $row = $rs->find_one();
        } catch(PDOException $e) {
            $this->fail("Caching is breaking subsequent queries!\n{$e->getMessage()}");
        }
        $this->assertEquals(array('class_id' => 1), $row->as_array());
    }

}
