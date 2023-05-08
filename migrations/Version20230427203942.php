<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427203942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tag_video (tag_id INT NOT NULL, video_id UUID NOT NULL, PRIMARY KEY(tag_id, video_id))');
        $this->addSql('CREATE INDEX IDX_5E2BC32ABAD26311 ON tag_video (tag_id)');
        $this->addSql('CREATE INDEX IDX_5E2BC32A29C1004E ON tag_video (video_id)');
        $this->addSql('COMMENT ON COLUMN tag_video.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tag_video ADD CONSTRAINT FK_5E2BC32ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag_video ADD CONSTRAINT FK_5E2BC32A29C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video DROP tags');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('ALTER TABLE tag_video DROP CONSTRAINT FK_5E2BC32ABAD26311');
        $this->addSql('ALTER TABLE tag_video DROP CONSTRAINT FK_5E2BC32A29C1004E');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_video');
        $this->addSql('ALTER TABLE video ADD tags TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN video.tags IS \'(DC2Type:array)\'');
    }
}
