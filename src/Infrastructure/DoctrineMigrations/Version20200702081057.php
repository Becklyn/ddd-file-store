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
 * @since  2020-07-02
 */
final class Version20200702081057 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds tables for use with becklyn/ddd-file-store';
    }

    public function up(Schema $schema) : void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isMySql = $platform instanceof MySQLPlatform;
        $isOracle = $platform instanceof OraclePlatform;
        $isSqlite = $platform instanceof SqlitePlatform;

        $this->skipIf(
            !$isMySql && !$isOracle && !$isSqlite,
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        if (!($isOracle)) {
            $this->addSql('CREATE TABLE becklyn_files (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, content_hash VARCHAR(255) NOT NULL, size INT UNSIGNED NOT NULL, owner_id VARCHAR(36) DEFAULT NULL, owner_type VARCHAR(255) DEFAULT NULL, created_ts DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_ts DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_6F8BCFACD17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE TABLE becklyn_filesystem_file_pointers (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(36) NOT NULL, file_id VARCHAR(36) NOT NULL, path VARCHAR(255) NOT NULL, created_ts DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_ts DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_BF1DDD44D17F50A6 (uuid), UNIQUE INDEX UNIQ_BF1DDD4493CB796C (file_id), UNIQUE INDEX UNIQ_BF1DDD44B548B0F (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Oracle gets handled in a future migration due to historical reasons
    }

    public function down(Schema $schema) : void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isMySql = $platform instanceof MySQLPlatform;
        $isOracle = $platform instanceof OraclePlatform;
        $isSqlite = $platform instanceof SqlitePlatform;

        $this->skipIf(
            !$isMySql && !$isOracle && !$isSqlite,
            'Migration can only be executed safely on \'MySQL\',  \'SQLite\' or \'Oracle\'.'
        );

        if (!$isOracle) {
            $this->addSql('DROP TABLE becklyn_files');
            $this->addSql('DROP TABLE becklyn_filesystem_file_pointers');
        }
        // Oracle gets handled in a future migration due to historical reasons
    }
}
