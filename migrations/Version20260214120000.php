<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ownership and tracking fields for groups, lessons, and quizzes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `group` ADD COLUMN IF NOT EXISTS created_at DATETIME NULL COMMENT '(DC2Type:datetime_immutable)', ADD COLUMN IF NOT EXISTS created_by VARCHAR(255) DEFAULT NULL");
        $this->addSql("UPDATE `group` SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE `group` MODIFY created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");

        $this->addSql("ALTER TABLE lesson ADD COLUMN IF NOT EXISTS target_group_id INT DEFAULT NULL, ADD COLUMN IF NOT EXISTS created_by_role VARCHAR(50) DEFAULT NULL, ADD COLUMN IF NOT EXISTS created_by_name VARCHAR(255) DEFAULT NULL, ADD COLUMN IF NOT EXISTS created_at DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("UPDATE lesson SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE lesson MODIFY created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("CREATE INDEX IF NOT EXISTS IDX_F87474FEE6804D4D ON lesson (target_group_id)");
        $this->addSql("ALTER TABLE lesson ADD CONSTRAINT FK_F87474FEE6804D4D FOREIGN KEY (target_group_id) REFERENCES `group` (id)");

        $this->addSql("ALTER TABLE quiz ADD COLUMN IF NOT EXISTS created_by_role VARCHAR(50) DEFAULT NULL, ADD COLUMN IF NOT EXISTS created_by_name VARCHAR(255) DEFAULT NULL, ADD COLUMN IF NOT EXISTS created_at DATETIME NULL COMMENT '(DC2Type:datetime_immutable)'");
        $this->addSql("UPDATE quiz SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE quiz MODIFY created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474FEE6804D4D');
        $this->addSql('DROP INDEX IDX_F87474FEE6804D4D ON lesson');
        $this->addSql('ALTER TABLE lesson DROP target_group_id, DROP created_by_role, DROP created_by_name, DROP created_at');
        $this->addSql('ALTER TABLE quiz DROP created_by_role, DROP created_by_name, DROP created_at');
        $this->addSql('ALTER TABLE `group` DROP created_at, DROP created_by');
    }
}
