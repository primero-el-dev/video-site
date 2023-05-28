<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230512183547 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_a4509aec23edc87');
        $this->addSql('ALTER TABLE user_subject_action ADD subject UUID NOT NULL');
        $this->addSql('ALTER TABLE user_subject_action DROP subject_id');
        $this->addSql('COMMENT ON COLUMN user_subject_action.subject IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_subject_action ADD subject_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_subject_action DROP subject');
        $this->addSql('CREATE INDEX idx_a4509aec23edc87 ON user_subject_action (subject_id)');
    }
}
