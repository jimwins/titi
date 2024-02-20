ChangeLog
---------

#### 0.0.7 - release 2024-02-20

* Tweak some function signatures for working with phpstan

#### 0.0.6 - release 2023-09-28

* Initial 8.2 support, silences deprecation warning

#### 0.0.5 - release 2023-08-12

* Add phpstan, clean things up to pass at level 5

#### 0.0.4 - release 2022-02-23

* Make ORM be JsonSerializable

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

