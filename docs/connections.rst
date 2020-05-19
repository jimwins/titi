Multiple Connections
====================

Titi can work with multiple conections. Most of the static functions
work with an optional connection name as an extra parameter. For the
``ORM::configure`` method, this means that when passing connection
strings for a new connection, the second parameter, which is typically
omitted, should be ``null``. In all cases, if a connection name is not
provided, it defaults to ``ORM::DEFAULT_CONNECTION``.

When chaining, once ``for_table()`` has been used in the chain, remaining
calls in the chain use the correct connection.

The connection to use can be specified in two separate ways. To indicate
a default connection key for a subclass of ``Model``, create a public static
property in your model class called ``$_connection_name``.

.. code-block:: php

    <?php
    // Default connection
    ORM::configure('sqlite:./example.db');

    // A named connection, where 'remote' is an arbitrary key name
    ORM::configure('mysql:host=localhost;dbname=my_database', null, 'remote');
    ORM::configure('username', 'database_user', 'remote');
    ORM::configure('password', 'top_secret', 'remote');

    // Using default connection
    $person = ORM::for_table('person')->find_one(5);

    // Using default connection, explicitly
    $person = ORM::for_table('person', ORM::DEFAULT_CONNECTION)->find_one(5);

    // Using named connection
    $person = ORM::for_table('different_person', 'remote')->find_one(5);

    // A named connection, where 'alternate' is an arbitray key name
    ORM::configure('sqlite:./example2.db', null, 'alternate');

    class SomeClass extends Model
    {
        public static $_connection_name = 'alternate';
    }

The connection to use can also be specified as an optional additional parameter to ``OrmWrapper::for_table()``, or to ``Model::factory()``. This will override the default setting (if any) found in the ``$_connection_name`` static property.

.. code-block:: php

    <?php
    $person = Model::factory('Author', 'alternate')->find_one(1);  // Uses connection named 'alternate'

The connection can be changed after a model is populated, should that be necessary:

.. code-block:: php

    <?php

    $person = Model::factory('Author')->find_one(1);     // Uses default connection
    $person->orm = Model::factory('Author', 'alternate');  // Switches to connection named 'alternate'
    $person->name = 'Foo';
    $person->save();                                     // *Should* now save through the updated connection

Notes
~~~~~
* **There is no support for joins across connections**
* As the Model methods ``has_one``, ``has_many`` and ``belongs_to`` don't require joins, these *should* work as expected, even when the objects on opposite sides of the relation belong to diffrent connections. The ``has_many_through`` relationship requires joins, and so will not reliably work across different connections.
* Multiple connections do not share configuration settings. This means if
  one connection has logging set to ``true`` and the other does not, only
  queries from the logged connection will be available via
  ``ORM::get_last_query()`` and ``ORM::get_query_log()``.
* ``ORM::get_connection_names()``, which returns an array of connection names.
* Caching *should* work with multiple connections (remember to turn caching
  on for each connection), but the unit tests are not robust. Please report
  any errors.

Supported Methods
^^^^^^^^^^^^^^^^^
In each of these cases, the ``$connection_name`` parameter is optional, and is
an arbitrary key identifying the named connection.

* ``ORM::configure($key, $value, $connection_name)``
* ``ORM::for_table($table_name, $connection_name)``
* ``ORM::set_db($pdo, $connection_name)``
* ``ORM::get_db($connection_name)``
* ``ORM::raw_execute($query, $parameters, $connection_name)``
* ``ORM::get_last_query($connection_name)``
* ``ORM::get_query_log($connection_name)``

Of these methods, only ``ORM::get_last_query($connection_name)`` does *not*
fallback to the default connection when no connection name is passed.
Instead, passing no connection name (or ``null``) returns the most recent
query on *any* connection.
