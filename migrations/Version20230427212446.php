<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427212446 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag ALTER name TYPE VARCHAR(140)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag ALTER name TYPE VARCHAR(255)');
    }
}
