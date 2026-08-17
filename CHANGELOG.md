5.1.0
=======

* (improvement) Widened `becklyn/ddd-symfony-bridge` to `^4.0 || ^5.0 || ^6.0`, allowing use with the Messenger-based 6.x bridge.
* (improvement) Widened `doctrine/orm` to `^2.10 || ^3.0` and `doctrine/doctrine-bundle` to `^2.4 || ^3.0`.
* (improvement) Added a return type to `Configuration::getConfigTreeBuilder()` for `ConfigurationInterface` compatibility on Symfony 7.
* (improvement) Raised the PHP minimum to 8.2, matching `becklyn/ddd-core` 4.x.

5.0.0
=======

* (feature) Add support for Symfony 7
* (feature) Add support for becklyn/ddd-symfony-bridge 5
* (BC) Drop support for becklyn/ddd-symfony-bridge 3

4.0.0
=======

* (bc) Renamed the File System Pointers table from becklyn_filesystem_file_pointers to becklyn_fs_file_pointers because the original longer name was causing problems with certain databases. To upgrade an existing installation, rename the table in your database manually.

3.0.2
=======

* (improvement) Support for 1.0 and 2.0 series of psr/log

3.0.1
=======

* (improvement) Support for 4.0 series of becklyn/ddd-core and becklyn/ddd-symfony-bridge

3.0.0
=======

* (bc) Support for latest version of becklyn/ddd-core and becklyn/ddd-symfony-bridge which provide event correlation and causation IDs.

2.0.2
=======

* (bug) Fixed migration requirements to work as intended.

2.0.1
=======

* (bug) Required becklyn/ddd-doctrine-bridge@2.2.1 in composer.json so that Oracle support actually works.

2.0.0
=======

* (feature) Added support for Oracle.
* (bc) No longer supports platforms other than MySQL, SQLite and Oracle even if the query syntax would work.
* (internal) The database tables no longer have an internal, DB-generated primary key field. The uuid is now the PK.

1.0.0
=======

* (feature) Initial release `\o/`
