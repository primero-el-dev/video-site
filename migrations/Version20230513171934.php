<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230513171934 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD "order" SERIAL');
        // $this->addSql('CREATE SEQUENCE comment_order_seq');
        // $this->addSql('ALTER TABLE comment ADD "order" INTEGER NOT NULL DEFAULT nextval(\'comment_order_seq\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP "order"');
        // $this->addSql('ALTER TABLE comment DROP "order"');
        // $this->addSql('DROP SEQUENCE comment_order_seq');
    }
}
