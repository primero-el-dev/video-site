<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427115349 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video ADD snapshot_timestamp DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE video ADD sample_start_timestamp DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE video DROP sample_path');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video DROP snapshot_timestamp');
        $this->addSql('ALTER TABLE video DROP sample_start_timestamp');
        $this->addSql('ALTER TABLE video ADD sample_path VARCHAR(255) NOT NULL');
    }
}
