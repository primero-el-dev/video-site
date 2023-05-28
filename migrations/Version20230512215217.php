<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230512215217 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_subject_action DROP CONSTRAINT fk_a4509aec23edc87');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_subject_action ADD CONSTRAINT fk_a4509aec23edc87 FOREIGN KEY (subject_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
