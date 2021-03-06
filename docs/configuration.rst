Configuration
=============

The first thing you need to know about Titi is that *you don’t need to
define any model classes to use it*. With almost every other ORM, the
first thing to do is set up your models and map them to database tables
(through configuration variables, XML files or similar). With Titi,
you can start using the ORM straight away.

Setup
~~~~~

If you have used Composer to include Titi in your project, you can use the
autoloader to provide access to the Titi namespace:

.. code-block:: php

    <?php
    require_once __DIR__ . '/vendor/autoload.php';

You can also use ``use`` to pull in the ORM and/or Model from the Titi
namespace (for an easy upgrade path from Idiorm and Paris).

.. code-block:: php

    <?php
    require_once __DIR__ . '/vendor/autoload.php';

    use \Titi\ORM, \Titi\Model;

(Further examples will assume that you have done this so we don't
need to keep explicitly referencing the Titi namespace.)

Then, pass a *Data Source Name* connection string to the ``configure``
method of the ``ORM`` class. This is used by PDO to connect to your
database. For more information, see the `PDO documentation`_.

.. code-block:: php

    <?php
    ORM::configure('sqlite:./example.db');

Configuration
~~~~~~~~~~~~~

Other than setting the DSN string for the database connection (see
above), the ``configure`` method can be used to set some other simple
options on the ORM class. Modifying settings involves passing a
key/value pair to the ``configure`` method, representing the setting you
wish to modify and the value you wish to set it to.

.. code-block:: php

    <?php
    ORM::configure('setting_name', 'value_for_setting');

A shortcut is provided to allow passing multiple key/value pairs at
once.

.. code-block:: php

    <?php
    ORM::configure(array(
        'setting_name_1' => 'value_for_setting_1',
        'setting_name_2' => 'value_for_setting_2',
        'etc' => 'etc'
    ));

Use the ``get_config`` method to read current settings.

.. code-block:: php

    <?php
    $isLoggingEnabled = ORM::get_config('logging');
    ORM::configure('logging', false);
    try {
      // some crazy loop we don't want to log
    } finally {
      ORM::configure('logging', $isLoggingEnabled);
    }

Database authentication details
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Settings: ``username`` and ``password``

Some database adapters (such as MySQL) require a username and password
to be supplied separately to the DSN string. (Although this is no longer
true as of PHP 7.4, where you can now specify these in the DSN for more
adapters.) These settings allow you to provide these values. A typical MySQL
connection setup might look like this:

.. code-block:: php

    <?php
    ORM::configure('mysql:host=localhost;dbname=my_database');
    ORM::configure('username', 'database_user');
    ORM::configure('password', 'top_secret');

Or you can combine the connection setup into a single line using the
configuration array shortcut:

.. code-block:: php

    <?php
    ORM::configure(array(
        'connection_string' => 'mysql:host=localhost;dbname=my_database',
        'username' => 'database_user',
        'password' => 'top_secret'
    ));

Result sets
^^^^^^^^^^^

Setting: ``return_result_sets``

Collections of results can be returned as an array (default) or as a result set.
See the `find_result_set()` documentation for more information.

.. code-block:: php

    <?php
    ORM::configure('return_result_sets', true); // returns result sets

.. note::

   It is recommended that you setup your projects to use result sets as they
   are more flexible.

PDO Driver Options
^^^^^^^^^^^^^^^^^^

Setting: ``driver_options``

Some database adapters require (or allow) an array of driver-specific
configuration options. This setting allows you to pass these options
through to the PDO constructor. For more information, see `the PDO
documentation`_. For example, to force the MySQL driver to use UTF-8 for
the connection:

.. code-block:: php

    <?php
    ORM::configure('driver_options', array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));

PDO Error Mode
^^^^^^^^^^^^^^

Setting: ``error_mode``

This can be used to set the ``PDO::ATTR_ERRMODE`` setting on the
database connection class used by Titi. It should be passed one of the
class constants defined by PDO. For example:

.. code-block:: php

    <?php
    ORM::configure('error_mode', PDO::ERRMODE_WARNING);

The default setting is ``PDO::ERRMODE_EXCEPTION``. For full details of
the error modes available, see `the PDO set attribute documentation`_.

PDO object access
^^^^^^^^^^^^^^^^^

Should it ever be necessary, the PDO object used by Titi may be
accessed directly through ``ORM::get_db()``, or set directly via
``ORM::set_db()``. This should be an unusual occurance.

After a statement has been executed by any means, such as ``::save()``
or ``::raw_execute()``, the ``PDOStatement`` instance used may be
accessed via ``ORM::get_last_statement()``. This may be useful in order
to access ``PDOStatement::errorCode()``, if PDO exceptions are turned
off, or to access the ``PDOStatement::rowCount()`` method, which returns
differing results based on the underlying database. For more
information, see the `PDOStatement documentation`_.

Identifier quote character
^^^^^^^^^^^^^^^^^^^^^^^^^^

Setting: ``identifier_quote_character``

Set the character used to quote identifiers (eg table name, column
name). If this is not set, it will be autodetected based on the database
driver being used by PDO.

ID Column
^^^^^^^^^

By default, the ORM assumes that all your tables have a primary key
column called ``id``. There are two ways to override this: for all
tables in the database, or on a per-table basis.

Setting: ``id_column``

This setting is used to configure the name of the primary key column for
all tables. If your ID column is called ``primary_key``, use:

.. code-block:: php

    <?php
    ORM::configure('id_column', 'primary_key');

You can specify a compound primary key using an array:

.. code-block:: php

    <?php
    ORM::configure('id_column', array('pk_1', 'pk_2'));

Note: If you use a auto-increment column in the compound primary key then it
should be the first one defined into the array.

Setting: ``id_column_overrides``

This setting is used to specify the primary key column name for each
table separately. It takes an associative array mapping table names to
column names. If, for example, your ID column names include the name of
the table, you can use the following configuration:

.. code-block:: php

    <?php
    ORM::configure('id_column_overrides', array(
        'person' => 'person_id',
        'role' => 'role_id',
    ));

As with ``id_column`` setting, you can specify a compound primary key
using an array.

Limit clause style
^^^^^^^^^^^^^^^^^^

Setting: ``limit_clause_style``

You can specify the limit clause style in the configuration. This is to
facilitate a MS SQL style limit clause that uses the ``TOP`` syntax.

Acceptable values are ``ORM::LIMIT_STYLE_TOP_N`` and ``ORM::LIMIT_STYLE_LIMIT``.

.. note::

    If the PDO driver you are using is one of sqlsrv, dblib or mssql then Titi
    will automatically select the ``ORM::LIMIT_STYLE_TOP_N`` for you unless you
    override the setting.

Query logging
^^^^^^^^^^^^^

Setting: ``logging``

Titi can log all queries it executes. To enable query logging, set the
``logging`` option to ``true`` (it is ``false`` by default).


Model prefixing
~~~~~~~~~~~~~~~

Setting: ``Model::$auto_prefix_models``

To save having type out model class name prefixes whenever code utilises ``Model::for_table()``
it is possible to specify a prefix that will be prepended onto the class name.

The model prefix is treated the same way as any other class name when the Model
attempts to convert it to a table name. This is documented in the :doc:`models`
section of the documentation.

Here is a namespaced example to make it clearer:

.. code-block:: php

    <?php
    Model::$auto_prefix_models = '\\Tests\\';
    Model::factory('Simple')->find_many(); // SQL executed: SELECT * FROM `tests_simple`
    Model::factory('SimpleUser')->find_many(); // SQL executed: SELECT * FROM `tests_simple_user`

Model prefixes are only compatible with the ``Model::factory()`` methods described above.
Where the shorter ``SimpleUser::find_many()`` style syntax is used, the addition of a
Model prefix will cause ``Class not found`` errors.

.. note::

    Model class property ``$_table`` sets an explicit table name, ignoring the
    ``$auto_prefix_models`` property in your individual model classes. See documentation in
    the :doc:`models` section of the documentation.

Model namespaces
~~~~~~~~~~~~~~~~

Setting: ``Model::$short_table_names``

Set as ``true`` to disregard namespace information when computing table names
from class names.

By default the class ``\Models\CarTyre`` expects the table name ``models_car_tyre``.
With ``Model::$short_table_names = true`` the class ``\Models\CarTyre`` expects the
table name ``car_tyre``.

.. code-block:: php

    <?php

    Model::$short_table_names = true;
    Model::factory('CarTyre')->find_many(); // SQL executed: SELECT * FROM `car_tyre`

    namespace Models {
        class CarTyre extends Model {

        }
    }

Further Configuration
~~~~~~~~~~~~~~~~~~~~~

The only other configuration options provided by Titi Models are the
``$_table`` and ``$_id_column`` static properties on model classes. To
configure the database connection, you should use the ORM’s configuration
system via the ``ORM::configure`` method.

If you are using multiple connections, the optional `$_connection_key` static property may also be used to provide a default string key indicating which database connection in `ORM` should be used.

Query logging
~~~~~~~~~~~~~

Titi can log all queries it executes. To enable query logging, set the
``logging`` option to ``true`` (it is ``false`` by default).

.. code-block:: php

    <?php
    ORM::configure('logging', true);

When query logging is enabled, you can use two static methods to access
the log. ``ORM::get_last_query()`` returns the most recent query
executed. ``ORM::get_query_log()`` returns an array of all queries
executed.

.. note::

    The code that does the query log is an approximation of that provided by
    PDO/the database (see the ORM source code for detail). The actual query
    isn't even available to log as the database/PDO handles the binding and
    doesn't pass it back.

    This means that you might come across some inconsistencies between what is
    logged and what is actually run. In these case you'll need to look at the
    query log provided by your database vendor (eg. MySQL).

Query logger
^^^^^^^^^^^^

Setting: ``logger``

.. note::

    You must enable ``logging`` for this setting to have any effect.

It is possible to supply a ``callable`` to this configuration setting, which
will be executed for every query that idiorm executes. In PHP a ``callable``
is anything that can be executed as if it were a function. Most commonly this
will take the form of a anonymous function.

This setting is useful if you wish to log queries with an external library as
it allows you too whatever you would like from inside the callback function.

.. code-block:: php

    <?php
    ORM::configure('logger', function($log_string, $query_time) {
        echo $log_string . ' in ' . $query_time;
    });

Query caching
^^^^^^^^^^^^^

Setting: ``caching``

Titi can cache the queries it executes during a request. To enable
query caching, set the ``caching`` option to ``true`` (it is ``false``
by default).

.. code-block:: php

    <?php
    ORM::configure('caching', true);


Setting: ``caching_auto_clear``

Titi's cache is never cleared by default. If you wish to automatically clear it on save, set ``caching_auto_clear`` to ``true``

.. code-block:: php

    <?php
    ORM::configure('caching_auto_clear', true);

When query caching is enabled, Titi will cache the results of every
``SELECT`` query it executes. If Titi encounters a query that has
already been run, it will fetch the results directly from its cache and
not perform a database query.

Warnings and gotchas
''''''''''''''''''''

-  Note that this is an in-memory cache that only persists data for the
   duration of a single request. This is *not* a replacement for a
   persistent cache such as `Memcached`_.

-  Titi’s cache is very simple, and does not attempt to invalidate
   itself when data changes. This means that if you run a query to
   retrieve some data, modify and save it, and then run the same query
   again, the results will be stale (ie, they will not reflect your
   modifications). This could potentially cause subtle bugs in your
   application. If you have caching enabled and you are experiencing odd
   behaviour, disable it and try again. If you do need to perform such
   operations but still wish to use the cache, you can call the
   ``ORM::clear_cache()`` to clear all existing cached queries.

-  Enabling the cache will increase the memory usage of your
   application, as all database rows that are fetched during each
   request are held in memory. If you are working with large quantities
   of data, you may wish to disable the cache.

Custom caching
''''''''''''''

If you wish to use custom caching functions, you can set them from the configure options.

.. code-block:: php

    <?php
    $my_cache = array();
    ORM::configure('cache_query_result', function ($cache_key, $value, $table_name, $connection_name) use (&$my_cache) {
        $my_cache[$cache_key] = $value;
    });
    ORM::configure('check_query_cache', function ($cache_key, $table_name, $connection_name) use (&$my_cache) {
        if(isset($my_cache[$cache_key])){
           return $my_cache[$cache_key];
        } else {
        return false;
        }
    });
    ORM::configure('clear_cache', function ($table_name, $connection_name) use (&$my_cache) {
         $my_cache = array();
    });

    ORM::configure('create_cache_key', function ($query, $parameters, $table_name, $connection_name) {
        $parameter_string = join(',', $parameters);
        $key = $query . ':' . $parameter_string;
        $my_key = 'my-prefix'.crc32($key);
        return $my_key;
    });


.. _PDO documentation: http://php.net/manual/en/pdo.construct.php
.. _the PDO documentation: http://php.net/manual/en/pdo.construct.php
.. _the PDO set attribute documentation: http://php.net/manual/en/pdo.setattribute.php
.. _PDOStatement documentation: http://php.net/manual/en/class.pdostatement.php
.. _Memcached: http://www.memcached.org/
