<?php

class QueryBuilderTest extends \PHPUnit\Framework\TestCase {

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

    public function testFindMany() {
        \Titi\ORM::for_table('widget')->find_many();
        $expected = "SELECT * FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testFindOne() {
        \Titi\ORM::for_table('widget')->find_one();
        $expected = "SELECT * FROM `widget` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testFindOneWithPrimaryKeyFilter() {
        \Titi\ORM::for_table('widget')->find_one(5);
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereIdIs() {
        \Titi\ORM::for_table('widget')->where_id_is(5)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereIdIn() {
        \Titi\ORM::for_table('widget')->where_id_in(array(4, 5))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `id` IN ('4', '5')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSingleWhereClause() {
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleWhereClauses() {
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereNotEqual() {
        \Titi\ORM::for_table('widget')->where_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` != 'Fred'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereLike() {
        \Titi\ORM::for_table('widget')->where_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereNotLike() {
        \Titi\ORM::for_table('widget')->where_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereIn() {
        \Titi\ORM::for_table('widget')->where_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereNotIn() {
        \Titi\ORM::for_table('widget')->where_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereAnyIs() {
        \Titi\ORM::for_table('widget')->where_any_is(array(
            array('name' => 'Joe', 'age' => 10),
            array('name' => 'Fred', 'age' => 20)))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `name` = 'Joe' AND `age` = '10' ) OR ( `name` = 'Fred' AND `age` = '20' ))";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereAnyIsOverrideOneColumn() {
        \Titi\ORM::for_table('widget')->where_any_is(array(
            array('name' => 'Joe', 'age' => 10),
            array('name' => 'Fred', 'age' => 20)), array('age' => '>'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `name` = 'Joe' AND `age` > '10' ) OR ( `name` = 'Fred' AND `age` > '20' ))";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereAnyIsOverrideAllOperators() {
        \Titi\ORM::for_table('widget')->where_any_is(array(
            array('score' => '5', 'age' => 10),
            array('score' => '15', 'age' => 20)), '>')->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `score` > '5' AND `age` > '10' ) OR ( `score` > '15' AND `age` > '20' ))";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLimit() {
        \Titi\ORM::for_table('widget')->limit(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLimitAndOffset() {
        \Titi\ORM::for_table('widget')->limit(5)->offset(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testOrderByDesc() {
        \Titi\ORM::for_table('widget')->order_by_desc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testOrderByAsc() {
        \Titi\ORM::for_table('widget')->order_by_asc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testOrderByExpression() {
        \Titi\ORM::for_table('widget')->order_by_expr('SOUNDEX(`name`)')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY SOUNDEX(`name`) LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleOrderBy() {
        \Titi\ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testGroupBy() {
        \Titi\ORM::for_table('widget')->group_by('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleGroupBy() {
        \Titi\ORM::for_table('widget')->group_by('name')->group_by('age')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testGroupByExpression() {
        \Titi\ORM::for_table('widget')->group_by_expr("FROM_UNIXTIME(`time`, '%Y-%m')")->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY FROM_UNIXTIME(`time`, '%Y-%m')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHaving() {
        \Titi\ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleHaving() {
        \Titi\ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->having('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingNotEqual() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` != 'Fred'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingLike() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingNotLike() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingIn() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingNotIn() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingLessThan() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_lt('age', 10)->having_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingLessThanOrEqualAndGreaterThanOrEqual() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_lte('age', 10)->having_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingNull() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NULL";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testHavingNotNull() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NOT NULL";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawHaving() {
        \Titi\ORM::for_table('widget')->group_by('name')->having_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testComplexQuery() {
        \Titi\ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' ORDER BY `name` ASC LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereLessThanAndGreaterThan() {
        \Titi\ORM::for_table('widget')->where_lt('age', 10)->where_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereLessThanAndEqualAndGreaterThanAndEqual() {
        \Titi\ORM::for_table('widget')->where_lte('age', 10)->where_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereNull() {
        \Titi\ORM::for_table('widget')->where_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereNotNull() {
        \Titi\ORM::for_table('widget')->where_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawWhereClause() {
        \Titi\ORM::for_table('widget')->where_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawWhereClauseWithPercentSign() {
        \Titi\ORM::for_table('widget')->where_raw('STRFTIME("%Y", "now") = ?', array(2012))->find_many();
        $expected = "SELECT * FROM `widget` WHERE STRFTIME(\"%Y\", \"now\") = '2012'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawWhereClauseWithNoParameters() {
        \Titi\ORM::for_table('widget')->where_raw('`name` = "Fred"')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawWhereClauseInMethodChain() {
        \Titi\ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND `size` = 'large'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawWhereClauseMultiples() {
        \Titi\ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where_raw('(`name` = ? OR `name` = ?)', array('Sarah', 'Jane'))->where('size', 'large')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND (`name` = 'Sarah' OR `name` = 'Jane') AND `size` = 'large'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawQuery() {
        \Titi\ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w')->find_many();
        $expected = "SELECT `w`.* FROM `widget` w";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawQueryWithParameters() {
        \Titi\ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->find_many();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawQueryWithNamedPlaceholders() {
        \Titi\ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = :name AND `age` = :age', array(':name' => 'Fred', ':age' => 5))->find_many();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSimpleResultColumn() {
        \Titi\ORM::for_table('widget')->select('name')->find_many();
        $expected = "SELECT `name` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleSimpleResultColumns() {
        \Titi\ORM::for_table('widget')->select('name')->select('age')->find_many();
        $expected = "SELECT `name`, `age` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSpecifyTableNameAndColumnInResultColumns() {
        \Titi\ORM::for_table('widget')->select('widget.name')->find_many();
        $expected = "SELECT `widget`.`name` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMainTableAlias() {
        \Titi\ORM::for_table('widget')->table_alias('w')->find_many();
        $expected = "SELECT * FROM `widget` `w`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testAliasesInResultColumns() {
        \Titi\ORM::for_table('widget')->select('widget.name', 'widget_name')->find_many();
        $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testAliasesInSelectManyResults() {
        \Titi\ORM::for_table('widget')->select_many(array('widget_name' => 'widget.name'), 'widget_handle')->find_many();
        $expected = "SELECT `widget`.`name` AS `widget_name`, `widget_handle` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLiteralExpressionInResultColumn() {
        \Titi\ORM::for_table('widget')->select_expr('COUNT(*)', 'count')->find_many();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLiteralExpressionInSelectManyResultColumns() {
        \Titi\ORM::for_table('widget')->select_many_expr(array('count' => 'COUNT(*)'), 'SUM(widget_order)')->find_many();
        $expected = "SELECT COUNT(*) AS `count`, SUM(widget_order) FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSimpleJoin() {
        \Titi\ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSimpleJoinWithWhereIdIsMethod() {
        \Titi\ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_one(5);
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` WHERE `widget`.`id` = '5' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testInnerJoin() {
        \Titi\ORM::for_table('widget')->inner_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testLeftOuterJoin() {
        \Titi\ORM::for_table('widget')->left_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` LEFT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRightOuterJoin() {
        \Titi\ORM::for_table('widget')->right_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` RIGHT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testFullOuterJoin() {
        \Titi\ORM::for_table('widget')->full_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
        $expected = "SELECT * FROM `widget` FULL OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMultipleJoinSources() {
        \Titi\ORM::for_table('widget')
        ->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))
        ->join('widget_nozzle', array('widget_nozzle.widget_id', '=', 'widget.id'))
        ->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` JOIN `widget_nozzle` ON `widget_nozzle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testJoinWithAliases() {
        \Titi\ORM::for_table('widget')->join('widget_handle', array('wh.widget_id', '=', 'widget.id'), 'wh')->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testJoinWithAliasesAndWhere() {
        \Titi\ORM::for_table('widget')->table_alias('w')->join('widget_handle', array('wh.widget_id', '=', 'w.id'), 'wh')->where_equal('id', 1)->find_many();
        $expected = "SELECT * FROM `widget` `w` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `w`.`id` WHERE `w`.`id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testJoinWithStringConstraint() {
        \Titi\ORM::for_table('widget')->join('widget_handle', "widget_handle.widget_id = widget.id")->find_many();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON widget_handle.widget_id = widget.id";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawJoin() {
        \Titi\ORM::for_table('widget')->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` )', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle')->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` ) `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawJoinWithParameters() {
        \Titi\ORM::for_table('widget')->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE '%button%' AND `widget_handle`.category = '2') `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testRawJoinAndRawWhereWithParameters() {
        \Titi\ORM::for_table('widget')
            ->raw_join('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))
            ->raw_join('INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE ?)', array('person.id', '=', 'widget.person_id'), 'person', array('%Fred%'))
            ->where_raw('`id` > ? AND `id` < ?', array(5, 10))
            ->find_many();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE '%button%' AND `widget_handle`.category = '2') `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE '%Fred%') `person` ON `person`.`id` = `widget`.`person_id` WHERE `id` > '5' AND `id` < '10'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSelectWithDistinct() {
        \Titi\ORM::for_table('widget')->distinct()->select('name')->find_many();
        $expected = "SELECT DISTINCT `name` FROM `widget`";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testInsertData() {
        $widget = \Titi\ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testInsertDataContainingAnExpression() {
        $widget = \Titi\ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`, `added`) VALUES ('Fred', '10', NOW())";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testInsertDataUsingArrayAccess() {
        $widget = \Titi\ORM::for_table('widget')->create();
        $widget['name'] = "Fred";
        $widget['age'] = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateData() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateDataContainingAnExpression() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW() WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateMultipleFields() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateMultipleFieldsContainingAnExpression() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = GeomFromText('POINT(1.2347 2.3436)') WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateMultipleFieldsContainingAnExpressionAndOverridePreviouslySetExpression() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->lat_long = 'unknown';
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = 'unknown' WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testDeleteData() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->delete();
        $expected = "DELETE FROM `widget` WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testDeleteMany() {
        \Titi\ORM::for_table('widget')->where_equal('age', 10)->delete_many();
        $expected = "DELETE FROM `widget` WHERE `age` = '10'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testCount() {
        \Titi\ORM::for_table('widget')->count();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }
    
    public function testIgnoreSelectAndCount() {
    	\Titi\ORM::for_table('widget')->select('test')->count();
    	$expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
    	$this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMax() {
        \Titi\ORM::for_table('person')->max('height');
        $expected = "SELECT MAX(`height`) AS `max` FROM `person` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testMin() {
        \Titi\ORM::for_table('person')->min('height');
        $expected = "SELECT MIN(`height`) AS `min` FROM `person` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testAvg() {
        \Titi\ORM::for_table('person')->avg('height');
        $expected = "SELECT AVG(`height`) AS `avg` FROM `person` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testSum() {
        \Titi\ORM::for_table('person')->sum('height');
        $expected = "SELECT SUM(`height`) AS `sum` FROM `person` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function test_quote_identifier_part() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set('added', '2013-01-04');
        $widget->save();
        $expected = "UPDATE `widget` SET `added` = '2013-01-04' WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }
    
    public function test_quote_multiple_identifiers_part() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $expected = "`id1`, `id2`";
        $this->assertEquals($expected, $record->_quote_identifier($record->_get_id_column_name()));
    }
    
    /**
     * Compound primary key tests
     */
    public function testFindOneWithCompoundPrimaryKey() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'));
        $record->findOne(array('id1' => 10, 'name' => 'Joe', 'id2' => 20));
        $expected = "SELECT * FROM `widget` WHERE `id1` = '10' AND `id2` = '20' LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testInsertWithCompoundPrimaryKey() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $expected = "INSERT INTO `widget` (`id1`, `id2`, `name`) VALUES ('10', '20', 'Joe')";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testUpdateWithCompoundPrimaryKey() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $record->set('name', 'John');
        $record->save();
        $expected = "UPDATE `widget` SET `name` = 'John' WHERE `id1` = '10' AND `id2` = '20'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testDeleteWithCompoundPrimaryKey() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'))->create();
        $record->set('id1', 10);
        $record->set('id2', 20);
        $record->set('name', 'Joe');
        $record->save();
        $record->delete();
        $expected = "DELETE FROM `widget` WHERE `id1` = '10' AND `id2` = '20'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testWhereIdInWithCompoundPrimaryKey() {
        $record = \Titi\ORM::for_table('widget')->use_id_column(array('id1', 'id2'));
        $record->where_id_in(array(
            array('id1' => 10, 'name' => 'Joe', 'id2' => 20),
            array('id1' => 20, 'name' => 'Joe', 'id2' => 30)))->find_many();
        $expected = "SELECT * FROM `widget` WHERE (( `id1` = '10' AND `id2` = '20' ) OR ( `id1` = '20' AND `id2` = '30' ))";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    /**
     * Regression tests
     */
    public function testIssue12IncorrectQuotingOfColumnWildcard() {
        \Titi\ORM::for_table('widget')->select('widget.*')->find_one();
        $expected = "SELECT `widget`.* FROM `widget` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testIssue57LogQueryRaisesWarningWhenPercentSymbolSupplied() {
        \Titi\ORM::for_table('widget')->where_raw('username LIKE "ben%"')->find_many();
        $expected = 'SELECT * FROM `widget` WHERE username LIKE "ben%"';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testIssue57LogQueryRaisesWarningWhenQuestionMarkSupplied() {
        \Titi\ORM::for_table('widget')->where_raw('comments LIKE "has been released?%"')->find_many();
        $expected = 'SELECT * FROM `widget` WHERE comments LIKE "has been released?%"';
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testIssue74EscapingQuoteMarksIn_quote_identifier_part() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set('ad`ded', '2013-01-04');
        $widget->save();
        $expected = "UPDATE `widget` SET `ad``ded` = '2013-01-04' WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testIssue90UsingSetExprAloneDoesTriggerQueryGeneration() {
        $widget = \Titi\ORM::for_table('widget')->find_one(1);
        $widget->set_expr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `added` = NOW() WHERE `id` = '1'";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }

    public function testIssue176LimitDoesntWorkFirstTime() {
        \Titi\ORM::reset_config();
        \Titi\ORM::reset_db();

        \Titi\ORM::configure('logging', true);
        \Titi\ORM::configure('connection_string', 'sqlite::memory:');

        \Titi\ORM::for_table('sqlite_master')->limit(1)->find_array();
        $expected = "SELECT * FROM `sqlite_master` LIMIT 1";
        $this->assertEquals($expected, \Titi\ORM::get_last_query());
    }
}

