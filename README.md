Titi
====

[![Build Status](https://travis-ci.org/jimwins/titi.png?branch=master)](https://travis-ci.org/jimws/titi)

Titi is a lightweight nearly-zero-configuration object-relational mapper and
fluent query builder for PHP, plus a lightweight ActiveRecord implementation.

Titi was forked from [Idiorm and Paris](http://j4mie.github.com/idiormandparis/)
(combined into a single repo).

---

Tested on PHP 8.1.16 and MySQL 8.0.32 - may work on earlier versions with PDO
and the correct database drivers.

Released under a [BSD license](http://en.wikipedia.org/wiki/BSD_licenses).

Features
--------

* Makes simple queries and simple CRUD operations completely painless.
* Gets out of the way when more complex SQL is required.
* Built on top of [PDO](http://php.net/pdo).
* Uses [prepared statements](http://php.net/manual/en/pdo.prepared-statements.php) throughout to protect against [SQL injection](http://en.wikipedia.org/wiki/SQL_injection) attacks.
* Requires no model classes, no XML configuration and no code generation: works out of the box, given only a connection string.
* Consists of one main class called `ORM`. Additional classes are prefixed with `Titi`. Minimal global namespace pollution.
* Database agnostic. Currently supports SQLite, MySQL, Firebird and PostgreSQL. May support others, please give it a try!
* Supports collections of models with method chaining to filter or apply actions to multiple results at once.
* Multiple connections supported
* PSR-1 compliant methods (any method can be called in camelCase instead of underscores eg. `find_many()` becomes `findMany()`) - you'll need PHP 5.3+

Documentation
-------------

The documentation is hosted on Read the Docs: [titi.rtfd.io](https://titi.readthedocs.io/).

### Building the Docs ###

You will need to install [Sphinx](http://sphinx-doc.org/) and then in the docs folder run:

    make html

The documentation will now be in docs/_build/html/index.html

Let's See Some Code
-------------------

```php
require 'vendor/autoload.php';

use \Titi\ORM;
use \Titi\Model;

$user = ORM::for_table('user')
    ->where_equal('username', 'j4mie')
    ->find_one();

$user->first_name = 'Jamie';
$user->save();

$tweets = ORM::for_table('tweet')
    ->select('tweet.*')
    ->join('user', array(
        'user.id', '=', 'tweet.user_id'
    ))
    ->where_equal('user.username', 'j4mie')
    ->find_many();

foreach ($tweets as $tweet) {
    echo $tweet->text;
}

/* ActiveRecord model */
class User extends Model {
    public function tweets() {
        return $this->has_many('Tweet');
    }
}

class Tweet extends Model {}

$user = Model::factory('User')
    ->where_equal('username', 'j4mie')
    ->find_one();
$user->first_name = 'Jamie';
$user->save();

$tweets = $user->tweets()->find_many();
foreach ($tweets as $tweet) {
    echo $tweet->text;
}
```

Tests
-----

Tests are written with PHPUnit and be run through composer

    composer test

To see the test progress and results:

    composer -v test
