<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230426161835 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video RENAME COLUMN clip_path TO sample_path');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video RENAME COLUMN sample_path TO clip_path');
    }
}
