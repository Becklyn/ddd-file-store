<?php

declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\Infrastructure\DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2022-07-15
 */
final class Version20220715092803 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Drops the database-generated internal id field as primary key and switches to uuid as primary key';
    }

    public function up(Schema $schema) : void
    {
        $platform = \get_class($this->connection->getDatabasePlatform());
        $this->skipIf(
            !\in_array($platform, [SqlitePlatform::class, MySQLPlatform::class, OraclePlatform::class], true),
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        // this data model is required in order for Oracle to work with same mapping as other platforms
        if (!($platform instanceof OraclePlatform)) {
            $this->addSql('ALTER TABLE becklyn_files MODIFY id INT NOT NULL');
            $this->addSql('DROP INDEX UNIQ_6F8BCFACD17F50A6 ON becklyn_files');
            $this->addSql('ALTER TABLE becklyn_files DROP PRIMARY KEY');
            $this->addSql('ALTER TABLE becklyn_files DROP id, CHANGE size file_size INT UNSIGNED NOT NULL');
            $this->addSql('ALTER TABLE becklyn_files ADD PRIMARY KEY (uuid)');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers MODIFY id INT NOT NULL');
            $this->addSql('DROP INDEX UNIQ_BF1DDD44D17F50A6 ON becklyn_filesystem_file_pointers');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers DROP PRIMARY KEY');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers DROP id');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers ADD PRIMARY KEY (uuid)');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers RENAME INDEX uniq_bf1ddd4493cb796c TO UNIQ_67255B4493CB796C');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers RENAME INDEX uniq_bf1ddd44b548b0f TO UNIQ_67255B44B548B0F');
        }

        // Oracle gets handled in a future migration due to historical reasons
    }

    public function preDown(Schema $schema) : void
    {
        $platform = \get_class($this->connection->getDatabasePlatform());
        $this->skipIf(
            !\in_array($platform, [SqlitePlatform::class, MySQLPlatform::class, OraclePlatform::class], true),
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        // this data model is required in order for Oracle to work with same mapping as other platforms
        if (!($platform instanceof OraclePlatform)) {
            $this->connection->executeQuery('ALTER TABLE becklyn_files ADD id INT DEFAULT NULL');
            $existingFiles = $this->connection->fetchAllAssociative('SELECT * FROM becklyn_files ORDER BY created_ts ASC');
            $id = 1;

            foreach ($existingFiles as $file) {
                $this->connection->executeQuery("UPDATE becklyn_files SET id = {$id} WHERE uuid = '{$file['uuid']}'");
                ++$id;
            }

            $this->connection->executeQuery('ALTER TABLE becklyn_filesystem_file_pointers ADD id INT DEFAULT NULL');
            $existingPointers = $this->connection->fetchAllAssociative('SELECT * FROM becklyn_filesystem_file_pointers ORDER BY created_ts ASC');
            $id = 1;

            foreach ($existingPointers as $pointer) {
                $this->connection->executeQuery("UPDATE becklyn_filesystem_file_pointers SET id = {$id} WHERE uuid = '{$pointer['uuid']}'");
                ++$id;
            }
        }

        // Oracle gets handled in a future migration due to historical reasons
    }

    public function down(Schema $schema) : void
    {
        $platform = \get_class($this->connection->getDatabasePlatform());
        $this->skipIf(
            !\in_array($platform, [SqlitePlatform::class, MySQLPlatform::class, OraclePlatform::class], true),
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        // this data model is required in order for Oracle to work with same mapping as other platforms
        if (!($platform instanceof OraclePlatform)) {
            $this->addSql('ALTER TABLE becklyn_files CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE file_size size INT UNSIGNED NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_6F8BCFACD17F50A6 ON becklyn_files (uuid)');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers CHANGE id id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_BF1DDD44D17F50A6 ON becklyn_filesystem_file_pointers (uuid)');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers RENAME INDEX uniq_67255b44b548b0f TO UNIQ_BF1DDD44B548B0F');
            $this->addSql('ALTER TABLE becklyn_filesystem_file_pointers RENAME INDEX uniq_67255b4493cb796c TO UNIQ_BF1DDD4493CB796C');
        }

        // Oracle gets handled in a future migration due to historical reasons
    }
}
