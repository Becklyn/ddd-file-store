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
