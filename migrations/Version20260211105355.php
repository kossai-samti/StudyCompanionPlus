<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211105355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_profile (id INT AUTO_INCREMENT NOT NULL, grade VARCHAR(50) NOT NULL, user_id INT DEFAULT NULL, student_group_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6C611FF7A76ED395 (user_id), INDEX IDX_6C611FF74DDF95DC (student_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_material (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, summary LONGTEXT DEFAULT NULL, flashcards LONGTEXT DEFAULT NULL, lesson_id INT NOT NULL, INDEX IDX_DF37601CCDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF74DDF95DC FOREIGN KEY (student_group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE study_material ADD CONSTRAINT FK_DF37601CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF7A76ED395');
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF74DDF95DC');
        $this->addSql('ALTER TABLE study_material DROP FOREIGN KEY FK_DF37601CCDF80196');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE student_profile');
        $this->addSql('DROP TABLE study_material');
        $this->addSql('DROP TABLE user');
    }
}
