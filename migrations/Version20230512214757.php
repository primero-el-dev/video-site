<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230512214757 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_subject_action RENAME COLUMN subject TO subject_id');
        $this->addSql('ALTER TABLE user_subject_action ADD CONSTRAINT FK_A4509AEC23EDC87 FOREIGN KEY (subject_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A4509AEC23EDC87 ON user_subject_action (subject_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_subject_action DROP CONSTRAINT FK_A4509AEC23EDC87');
        $this->addSql('DROP INDEX IDX_A4509AEC23EDC87');
        $this->addSql('ALTER TABLE user_subject_action RENAME COLUMN subject_id TO subject');
    }
}
