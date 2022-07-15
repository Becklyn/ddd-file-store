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
final class Version20220715110028 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds Oracle support';
    }

    public function up(Schema $schema) : void
    {
        $platform = \get_class($this->connection->getDatabasePlatform());
        $this->skipIf(
            !\in_array($platform, [SqlitePlatform::class, MySQLPlatform::class, OraclePlatform::class], true),
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        if ($platform instanceof OraclePlatform) {
            $this->addSql('CREATE TABLE becklyn_files (uuid VARCHAR2(36) NOT NULL, filename VARCHAR2(255) NOT NULL, content_hash VARCHAR2(255) NOT NULL, file_size NUMBER(10) NOT NULL, owner_id VARCHAR2(36) DEFAULT NULL NULL, owner_type VARCHAR2(255) DEFAULT NULL NULL, created_ts TIMESTAMP(6) NOT NULL, updated_ts TIMESTAMP(6) NOT NULL, PRIMARY KEY(uuid))');
            $this->addSql('CREATE TABLE becklyn_filesystem_file_pointers (uuid VARCHAR2(36) NOT NULL, file_id VARCHAR2(36) NOT NULL, path VARCHAR2(255) NOT NULL, created_ts TIMESTAMP(6) NOT NULL, updated_ts TIMESTAMP(6) NOT NULL, PRIMARY KEY(uuid))');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_67255B4493CB796C ON becklyn_filesystem_file_pointers (file_id)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_67255B44B548B0F ON becklyn_filesystem_file_pointers (path)');
        }

        // Other supported platforms have been handled in previous migrations
    }

    public function down(Schema $schema) : void
    {
        $platform = \get_class($this->connection->getDatabasePlatform());
        $this->skipIf(
            !\in_array($platform, [SqlitePlatform::class, MySQLPlatform::class, OraclePlatform::class], true),
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        if ($platform instanceof OraclePlatform) {
            $this->addSql('DROP TABLE becklyn_files');
            $this->addSql('DROP TABLE becklyn_filesystem_file_pointers');
        }

        // Other supported platforms have been handled in previous migrations
    }
}
