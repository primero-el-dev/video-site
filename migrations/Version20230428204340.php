<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230428204340 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video DROP CONSTRAINT fk_7cc7da2cb03a8386');
        $this->addSql('DROP INDEX idx_7cc7da2cb03a8386');
        $this->addSql('ALTER TABLE video RENAME COLUMN created_by_id TO owner_id');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7CC7DA2C7E3C61F9 ON video (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video DROP CONSTRAINT FK_7CC7DA2C7E3C61F9');
        $this->addSql('DROP INDEX IDX_7CC7DA2C7E3C61F9');
        $this->addSql('ALTER TABLE video RENAME COLUMN owner_id TO created_by_id');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT fk_7cc7da2cb03a8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7cc7da2cb03a8386 ON video (created_by_id)');
    }
}
