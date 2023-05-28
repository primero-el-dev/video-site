<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230514200544 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification ALTER action TYPE VARCHAR(70)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification ALTER action TYPE VARCHAR(255)');
    }
}
