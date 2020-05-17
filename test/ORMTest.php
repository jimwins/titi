<?php

class ORMTest extends \PHPUnit\Framework\TestCase {

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

    public function testStaticAtrributes() {
        $this->assertEquals('0', \Titi\ORM::CONDITION_FRAGMENT);
        $this->assertEquals('1', \Titi\ORM::CONDITION_VALUES);
    }

    public function testForTable() {
        $result = \Titi\ORM::for_table('test');
        $this->assertInstanceOf('\Titi\ORM', $result);
    }

    public function testCreate() {
        $model = \Titi\ORM::for_table('test')->create();
        $this->assertInstanceOf('\Titi\ORM', $model);
        $this->assertTrue($model->is_new());
    }

    public function testIsNew() {
        $model = \Titi\ORM::for_table('test')->create();
        $this->assertTrue($model->is_new());

        $model = \Titi\ORM::for_table('test')->create(array('test' => 'test'));
        $this->assertTrue($model->is_new());
    }

    public function testIsDirty() {
        $model = \Titi\ORM::for_table('test')->create();
        $this->assertFalse($model->is_dirty('test'));

        $model = \Titi\ORM::for_table('test')->create(array('test' => 'test'));
        $this->assertTrue($model->is_dirty('test'));

        $model->test = null;
        $this->assertTrue($model->is_dirty('test'));

        $model->test = '';
        $this->assertTrue($model->is_dirty('test'));
    }

    public function testArrayAccess() {
        $value = 'test';
        $model = \Titi\ORM::for_table('test')->create();
        $model['test'] = $value;
        $this->assertTrue(isset($model['test']));
        $this->assertEquals($model['test'], $value);
        unset($model['test']);
        $this->assertFalse(isset($model['test']));
    }

    public function testFindResultSet() {
        $result_set = \Titi\ORM::for_table('test')->find_result_set();
        $this->assertInstanceOf('\Titi\ResultSet', $result_set);
        $this->assertSame(count($result_set), 5);
    }

    public function testFindResultSetByDefault() {
        \Titi\ORM::configure('return_result_sets', true);

        $result_set = \Titi\ORM::for_table('test')->find_many();
        $this->assertInstanceOf('\Titi\ResultSet', $result_set);
        $this->assertSame(count($result_set), 5);
        
        \Titi\ORM::configure('return_result_sets', false);
        
        $result_set = \Titi\ORM::for_table('test')->find_many();
        $this->assertIsArray($result_set);
        $this->assertSame(count($result_set), 5);
    }

    public function testGetLastPdoStatement() {
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->find_one();
        $statement = \Titi\ORM::get_last_statement();
        $this->assertInstanceOf('MockPDOStatement', $statement);
    }

    /**
     * @expectedException MethodMissingException
     */
    public function testInvalidORMFunctionCallShouldCreateException() {
        $this->expectException(\Titi\MethodMissingException::class);
        $orm = \Titi\ORM::for_table('test');
        $orm->invalidFunctionCall();
    }

    /**
     * @expectedException MethodMissingException
     */
    public function testInvalidResultsSetFunctionCallShouldCreateException() {
        $this->expectException(\Titi\MethodMissingException::class);
        $resultSet = \Titi\ORM::for_table('test')->find_result_set();
        $resultSet->invalidFunctionCall();
    }

    /**
     * These next two tests are needed because if you have select()ed some fields,
     * but not the primary key, then the primary key is not available for the
     * update/delete query - see issue #203.
     * We need to change the primary key here to something other than `id`
     * becuase MockPDOStatement->fetch() always returns an id.
     */
    public function testUpdateNullPrimaryKey() {
        try {
            $widget = \Titi\ORM::for_table('widget')
                ->use_id_column('primary')
                ->select('foo')
                ->where('primary', 1)
                ->find_one()
            ;

            $widget->foo = 'bar';
            $widget->save();

            throw new \Exception('Test did not throw expected exception');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Primary key ID missing from row or is null');
        }
    }

    public function testDeleteNullPrimaryKey() {
        try {
            $widget = \Titi\ORM::for_table('widget')
                ->use_id_column('primary')
                ->select('foo')
                ->where('primary', 1)
                ->find_one()
            ;

            $widget->delete();

            throw new \Exception('Test did not throw expected exception');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Primary key ID missing from row or is null');
        }
    }

    public function testNullPrimaryKey() {
        try {
            $widget = \Titi\ORM::for_table('widget')
                ->use_id_column('primary')
                ->select('foo')
                ->where('primary', 1)
                ->find_one()
            ;

            $widget->id(true);

            throw new \Exception('Test did not throw expected exception');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Primary key ID missing from row or is null');
        }
    }

    public function testNullPrimaryKeyPart() {
        try {
            $widget = \Titi\ORM::for_table('widget')
                ->use_id_column(array('id', 'primary'))
                ->select('foo')
                ->where('id', 1)
                ->where('primary', 1)
                ->find_one()
            ;

            $widget->id(true);

            throw new \Exception('Test did not throw expected exception');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Primary key ID contains null value(s)');
        }
    }
}
