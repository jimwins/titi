ChangeLog
---------

#### 0.0.3 - release 2020-06-22

* Make ResultSet be JsonSerializable
* Use PDOStatement::fetchAll() instead of doing our own loop around
  PDOStatement::fetch()

#### 0.0.2 - release 2020-05-18

* Cleaned up documentation

#### 0.0.1 - release 2020-05-17

The tests pass (on PHP 7.3 and 7.4, but that just means there is inadequate
testing). The documentation is a very rough merge of the original
documentation and may not even build, it will need some reorganization.

* Initial fork from Idiorm 1.5.7 and Paris 1.5.6, see their respective
  documentation for changes before the fork

