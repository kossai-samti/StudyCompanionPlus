<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216140616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT NULL, teacher_id INT DEFAULT NULL, INDEX IDX_6DC044C541807E1D (teacher_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, difficulty VARCHAR(255) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, created_by_role VARCHAR(50) DEFAULT NULL, created_by_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, target_group_id INT DEFAULT NULL, INDEX IDX_F87474F324FF092E (target_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE performance_report (id INT AUTO_INCREMENT NOT NULL, quiz_score DOUBLE PRECISION NOT NULL, weak_topics LONGTEXT DEFAULT NULL, mastery_status VARCHAR(50) NOT NULL, student_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_A4C759E6CB944F1A (student_id), INDEX IDX_A4C759E6CDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, options JSON NOT NULL, correct_answer VARCHAR(255) NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, difficulty VARCHAR(255) NOT NULL, created_by_role VARCHAR(50) DEFAULT NULL, created_by_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, lesson_id INT NOT NULL, INDEX IDX_A412FA92CDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_answer (id INT AUTO_INCREMENT NOT NULL, answer VARCHAR(255) NOT NULL, is_correct TINYINT NOT NULL, student_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_54EB92A5CB944F1A (student_id), INDEX IDX_54EB92A51E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_profile (id INT AUTO_INCREMENT NOT NULL, grade VARCHAR(50) NOT NULL, user_id INT DEFAULT NULL, student_group_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6C611FF7A76ED395 (user_id), INDEX IDX_6C611FF74DDF95DC (student_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_material (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, summary LONGTEXT DEFAULT NULL, flashcards LONGTEXT DEFAULT NULL, lesson_id INT NOT NULL, INDEX IDX_DF37601CCDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE teacher_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, teacher_id INT NOT NULL, student_id INT NOT NULL, INDEX IDX_59B6DF2D41807E1D (teacher_id), INDEX IDX_59B6DF2DCB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE teacher_profile (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, teacher_profile_id INT DEFAULT NULL, INDEX IDX_8D93D64946E5B018 (teacher_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C541807E1D FOREIGN KEY (teacher_id) REFERENCES teacher_profile (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F324FF092E FOREIGN KEY (target_group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE performance_report ADD CONSTRAINT FK_A4C759E6CB944F1A FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE performance_report ADD CONSTRAINT FK_A4C759E6CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE student_answer ADD CONSTRAINT FK_54EB92A5CB944F1A FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_answer ADD CONSTRAINT FK_54EB92A51E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student_profile ADD CONSTRAINT FK_6C611FF74DDF95DC FOREIGN KEY (student_group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE study_material ADD CONSTRAINT FK_DF37601CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE teacher_comment ADD CONSTRAINT FK_59B6DF2D41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher_profile (id)');
        $this->addSql('ALTER TABLE teacher_comment ADD CONSTRAINT FK_59B6DF2DCB944F1A FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64946E5B018 FOREIGN KEY (teacher_profile_id) REFERENCES teacher_profile (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C541807E1D');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F324FF092E');
        $this->addSql('ALTER TABLE performance_report DROP FOREIGN KEY FK_A4C759E6CB944F1A');
        $this->addSql('ALTER TABLE performance_report DROP FOREIGN KEY FK_A4C759E6CDF80196');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92CDF80196');
        $this->addSql('ALTER TABLE student_answer DROP FOREIGN KEY FK_54EB92A5CB944F1A');
        $this->addSql('ALTER TABLE student_answer DROP FOREIGN KEY FK_54EB92A51E27F6BF');
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF7A76ED395');
        $this->addSql('ALTER TABLE student_profile DROP FOREIGN KEY FK_6C611FF74DDF95DC');
        $this->addSql('ALTER TABLE study_material DROP FOREIGN KEY FK_DF37601CCDF80196');
        $this->addSql('ALTER TABLE teacher_comment DROP FOREIGN KEY FK_59B6DF2D41807E1D');
        $this->addSql('ALTER TABLE teacher_comment DROP FOREIGN KEY FK_59B6DF2DCB944F1A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64946E5B018');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE performance_report');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE student_answer');
        $this->addSql('DROP TABLE student_profile');
        $this->addSql('DROP TABLE study_material');
        $this->addSql('DROP TABLE teacher_comment');
        $this->addSql('DROP TABLE teacher_profile');
        $this->addSql('DROP TABLE user');
    }
}
