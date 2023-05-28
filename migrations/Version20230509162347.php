<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230509162347 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE user_video_view_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_video_view (id INT NOT NULL, video_id UUID NOT NULL, user_id UUID DEFAULT NULL, ip VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E0D30BE29C1004E ON user_video_view (video_id)');
        $this->addSql('CREATE INDEX IDX_5E0D30BEA76ED395 ON user_video_view (user_id)');
        $this->addSql('COMMENT ON COLUMN user_video_view.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_video_view.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_video_view ADD CONSTRAINT FK_5E0D30BE29C1004E FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_view ADD CONSTRAINT FK_5E0D30BEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_video_view_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_video_view DROP CONSTRAINT FK_5E0D30BE29C1004E');
        $this->addSql('ALTER TABLE user_video_view DROP CONSTRAINT FK_5E0D30BEA76ED395');
        $this->addSql('DROP TABLE user_video_view');
    }
}
