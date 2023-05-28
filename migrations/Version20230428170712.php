<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230428170712 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_389B7835E237E06 ON tag (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_389B7835E237E06');
    }
}
