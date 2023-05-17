3.0.3
=======

* (bc) Rename the File System Pointers Table to a shorter Version

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
