<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427200943 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video ALTER tags DROP DEFAULT');
        $this->addSql('ALTER TABLE video ALTER tags DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video ALTER tags SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE video ALTER tags SET NOT NULL');
    }
}
