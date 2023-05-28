<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230516120658 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_video_view_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE playlist_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_view_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE playlist (id UUID NOT NULL, owner_id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D782112D7E3C61F9 ON playlist (owner_id)');
        $this->addSql('COMMENT ON COLUMN playlist.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN playlist.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN playlist.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE playlist_item (id INT NOT NULL, playlist_id UUID NOT NULL, video_id UUID NOT NULL, "order" INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF02127C6BBD148 ON playlist_item (playlist_id)');
        $this->addSql('CREATE INDEX IDX_BF02127C29C1004E ON playlist_item (video_id)');
        $this->addSql('COMMENT ON COLUMN playlist_item.playlist_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN playlist_item.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_view (id INT NOT NULL, subject_id UUID NOT NULL, user_id UUID DEFAULT NULL, ip VARCHAR(30) DEFAULT NULL, subject_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_847CE74723EDC87 ON user_view (subject_id)');
        $this->addSql('CREATE INDEX IDX_847CE747A76ED395 ON user_view (user_id)');
        $this->addSql('COMMENT ON COLUMN user_view.subject_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_view.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE playlist ADD CONSTRAINT FK_D782112D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE playlist_item ADD CONSTRAINT FK_BF02127C6BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE playlist_item ADD CONSTRAINT FK_BF02127C29C1004E FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_view ADD CONSTRAINT FK_847CE74723EDC87 FOREIGN KEY (subject_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_view ADD CONSTRAINT FK_847CE747A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_view DROP CONSTRAINT fk_5e0d30be29c1004e');
        $this->addSql('ALTER TABLE user_video_view DROP CONSTRAINT fk_5e0d30bea76ed395');
        $this->addSql('DROP TABLE user_video_view');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE playlist_item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_view_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_video_view_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_video_view (id INT NOT NULL, video_id UUID NOT NULL, user_id UUID DEFAULT NULL, ip VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5e0d30bea76ed395 ON user_video_view (user_id)');
        $this->addSql('CREATE INDEX idx_5e0d30be29c1004e ON user_video_view (video_id)');
        $this->addSql('COMMENT ON COLUMN user_video_view.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_video_view.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_video_view ADD CONSTRAINT fk_5e0d30be29c1004e FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_view ADD CONSTRAINT fk_5e0d30bea76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE playlist DROP CONSTRAINT FK_D782112D7E3C61F9');
        $this->addSql('ALTER TABLE playlist_item DROP CONSTRAINT FK_BF02127C6BBD148');
        $this->addSql('ALTER TABLE playlist_item DROP CONSTRAINT FK_BF02127C29C1004E');
        $this->addSql('ALTER TABLE user_view DROP CONSTRAINT FK_847CE74723EDC87');
        $this->addSql('ALTER TABLE user_view DROP CONSTRAINT FK_847CE747A76ED395');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE playlist_item');
        $this->addSql('DROP TABLE user_view');
    }
}
