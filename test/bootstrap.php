<?php

require dirname(__FILE__).'/../vendor/autoload.php';

use Titi\ORM;
use Titi\Model;

/**
 *
 * Mock version of the PDOStatement class.
 *
 */
class MockPDOStatement extends PDOStatement {
   private $current_row = 0;
   private $statement = NULL;
   private $bindParams = [];
   
   /**
    * Store the statement that gets passed to the constructor
    */
   public function __construct($statement) {
       $this->statement = $statement;
   }

   /**
    * Check that the array
    */
   public function execute($params = NULL) : bool {
       $count = 0;
       $m = [];
       if (is_null($params)) $params = $this->bindParams;
       if (preg_match_all('/"[^"\\\\]*(?:\\?)[^"\\\\]*"|\'[^\'\\\\]*(?:\\?)[^\'\\\\]*\'|(\\?)/', $this->statement, $m, PREG_SET_ORDER)) {
           $count = count($m);
           for ($v = 0; $v < $count; $v++) {
               if (count($m[$v]) == 1) unset($m[$v]);
           }
           $count = count($m);
           for ($i = 0; $i < $count; $i++) {
               if (!isset($params[$i])) {
                   ob_start();
                   var_dump($m, $params);
                   $output = ob_get_clean();
                   throw new Exception('Incorrect parameter count. Expected ' . $count . ' got ' . count($params) . ".\n" . $this->statement . "\n" . $output);
               }
           }
       }

       return true;
   }

   /**
    * Add data to arrays
    */
   public function bindParam($paramno, &$param, $type = NULL, $maxlen = NULL, $driverdata = NULL) : bool
   {
       // Do check on type
       if (!is_int($type) || ($type != PDO::PARAM_STR && $type != PDO::PARAM_NULL && $type != PDO::PARAM_BOOL && $type != PDO::PARAM_INT))
           throw new Exception('Incorrect parameter type. Expected $type to be an integer.');

       // Add param to array
       $this->bindParams[is_int($paramno) ? --$paramno : $paramno] = $param;

       return true;
   }
   
   /**
    * Return some dummy data
    */
   public function fetch($fetch_style=PDO::FETCH_BOTH, $cursor_orientation=PDO::FETCH_ORI_NEXT, $cursor_offset=0) : mixed {
       if ($this->current_row == 5) {
           return false;
       } else {
           return [ 'name' => 'Fred', 'age' => 10, 'id' => ++$this->current_row ];
       }
   }

   public function fetchAll($fetch_style=PDO::FETCH_BOTH, mixed ...$args) : array {
        $rows = [];
        while ($row= $this->fetch($fetch_style)) {
            $rows[] = $row;
        }
        return $rows;
   }
}

/**
 *
 * Mock database class implementing a subset
 * of the PDO API.
 *
 */
class MockPDO extends PDO {
   /**
    * Return a dummy PDO statement
    */
   private $last_query;
   public function prepare($statement, $driver_options=[]) : PDOStatement|false {
       $this->last_query = new MockPDOStatement($statement);
       return $this->last_query;
   }
}

/**
 * Another mock PDOStatement class, used for testing multiple connections
 */
class MockDifferentPDOStatement extends MockPDOStatement {}

/**
 * A different mock database class, for testing multiple connections
 * Mock database class implementing a subset of the PDO API.
 */
class MockDifferentPDO extends MockPDO {

    /**
     * Return a dummy PDO statement
     */
    private $last_query;
    public function prepare($statement, $driver_options = []) : PDOStatement|false {
        $this->last_query = new MockDifferentPDOStatement($statement);
        return $this->last_query;
    }
}

class MockMsSqlPDO extends MockPDO {

   public $fake_driver = 'mssql';

   /**
    * If we are asking for the name of the driver, check if a fake one
    * has been set.
    */
    public function getAttribute($attribute) : bool {
        if ($attribute == self::ATTR_DRIVER_NAME) {
            if (!is_null($this->fake_driver)) {
                return $this->fake_driver;
            }
        }
        
        return parent::getAttribute($attribute);
    }
    
}

/**
 * Models for use during testing
 */
class Simple extends \Titi\Model { }
class ComplexModelClassName extends \Titi\Model { }
class ModelWithCustomTable extends \Titi\Model {
    public static $_table = 'custom_table';
}
class ModelWithCustomTableAndCustomIdColumn extends \Titi\Model {
    public static $_table = 'custom_table';
    public static $_id_column = 'custom_id_column';
}
class ModelWithFilters extends \Titi\Model {
    public static function name_is_fred($orm) {
        return $orm->where('name', 'Fred');
    }
    public static function name_is($orm, $name) {
        return $orm->where('name', $name);
    }
}
class ModelWithCustomConnection extends \Titi\Model {
    const ALTERNATE = 'alternate';
    public static $_connection_name = self::ALTERNATE;
}

class Profile extends \Titi\Model {
    public function user() {
        return $this->belongs_to('User');
    }
} 
class User extends \Titi\Model {
    public function profile() {
        return $this->has_one('Profile');
    }
}
class UserTwo extends \Titi\Model {
    public function profile() {
        return $this->has_one('Profile', 'my_custom_fk_column');
    }
}
class UserFive extends \Titi\Model {
    public function profile() {
        return $this->has_one('Profile', 'my_custom_fk_column', 'name');
    }
}
class ProfileTwo extends \Titi\Model {
    public function user() {
        return $this->belongs_to('User', 'custom_user_fk_column');
    }
}
class ProfileThree extends \Titi\Model {
    public function user() {
        return $this->belongs_to('User', 'custom_user_fk_column', 'name');
    }
}
class Post extends \Titi\Model { }
class UserThree extends \Titi\Model {
    public function posts() {
        return $this->has_many('Post');
    }
}
class UserFour extends \Titi\Model {
    public function posts() {
        return $this->has_many('Post', 'my_custom_fk_column');
    }
}
class UserSix extends \Titi\Model {
    public function posts() {
        return $this->has_many('Post', 'my_custom_fk_column', 'name');
    }
}
class Author extends \Titi\Model { }
class AuthorBook extends \Titi\Model { }
class Book extends \Titi\Model {
    public function authors() {
        return $this->has_many_through('Author');
    }
}
class BookTwo extends \Titi\Model {
    public function authors() {
        return $this->has_many_through('Author', 'AuthorBook', 'custom_book_id', 'custom_author_id');
    }
}
class BookThree extends \Titi\Model {
    public function authors() {
        return $this->has_many_through('Author', 'AuthorBook', 'custom_book_id', 'custom_author_id', 'custom_book_id_in_book_table', 'custom_author_id_in_author_table');
    }
}
class BookFour extends \Titi\Model {
    public function authors() {
        return $this->has_many_through('Author', 'AuthorBook', 'custom_book_id', 'custom_author_id', null, 'custom_author_id_in_author_table');
    }
}
class BookFive extends \Titi\Model {
    public function authors() {
        return $this->has_many_through('Author', 'AuthorBook', 'custom_book_id', 'custom_author_id', 'custom_book_id_in_book_table');
    }
}
class MockPrefix_Simple extends \Titi\Model { } 
class MockPrefix_TableSpecified extends \Titi\Model {
    public static $_table = 'simple';
} 
