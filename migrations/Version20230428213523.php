<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230428213523 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD photo_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD background_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD birth DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP photo_path');
        $this->addSql('ALTER TABLE "user" DROP background_path');
        $this->addSql('ALTER TABLE "user" DROP birth');
    }
}
