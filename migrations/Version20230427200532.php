<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427200532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('ALTER TABLE video_tag DROP CONSTRAINT fk_f910728729c1004e');
        $this->addSql('ALTER TABLE video_tag DROP CONSTRAINT fk_f9107287bad26311');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE video_tag');
        $this->addSql('ALTER TABLE video ADD tags TEXT NOT NULL DEFAULT \'[]\'');
        $this->addSql('COMMENT ON COLUMN video.tags IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, name VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE video_tag (video_id UUID NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(video_id, tag_id))');
        $this->addSql('CREATE INDEX idx_f9107287bad26311 ON video_tag (tag_id)');
        $this->addSql('CREATE INDEX idx_f910728729c1004e ON video_tag (video_id)');
        $this->addSql('COMMENT ON COLUMN video_tag.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE video_tag ADD CONSTRAINT fk_f910728729c1004e FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video_tag ADD CONSTRAINT fk_f9107287bad26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video DROP tags');
    }
}
