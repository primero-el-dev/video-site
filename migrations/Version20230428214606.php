<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230428214606 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE token DROP CONSTRAINT fk_5f37a13ba76ed395');
        $this->addSql('DROP INDEX idx_5f37a13ba76ed395');
        $this->addSql('ALTER TABLE token RENAME COLUMN user_id TO owner_id');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5F37A13B7E3C61F9 ON token (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13B7E3C61F9');
        $this->addSql('DROP INDEX IDX_5F37A13B7E3C61F9');
        $this->addSql('ALTER TABLE token RENAME COLUMN owner_id TO user_id');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT fk_5f37a13ba76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5f37a13ba76ed395 ON token (user_id)');
    }
}
