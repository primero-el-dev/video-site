<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230428232032 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER nick SET NOT NULL');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN birth TO birth_date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER nick DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN birth_date TO birth');
    }
}
