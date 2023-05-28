<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230512172603 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_video_rating_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_comment_rating_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_video_rating DROP CONSTRAINT fk_6012faeda76ed395');
        $this->addSql('ALTER TABLE user_video_rating DROP CONSTRAINT fk_6012faed29c1004e');
        $this->addSql('ALTER TABLE user_comment_rating DROP CONSTRAINT fk_791fa169a76ed395');
        $this->addSql('ALTER TABLE user_comment_rating DROP CONSTRAINT fk_791fa169f8697d13');
        $this->addSql('DROP TABLE user_video_rating');
        $this->addSql('DROP TABLE user_comment_rating');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE user_video_rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_comment_rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_video_rating (id INT NOT NULL, user_id UUID NOT NULL, video_id UUID NOT NULL, rating INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_6012faed29c1004e ON user_video_rating (video_id)');
        $this->addSql('CREATE INDEX idx_6012faeda76ed395 ON user_video_rating (user_id)');
        $this->addSql('COMMENT ON COLUMN user_video_rating.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_video_rating.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_comment_rating (id INT NOT NULL, user_id UUID NOT NULL, comment_id INT NOT NULL, rating INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_791fa169f8697d13 ON user_comment_rating (comment_id)');
        $this->addSql('CREATE INDEX idx_791fa169a76ed395 ON user_comment_rating (user_id)');
        $this->addSql('COMMENT ON COLUMN user_comment_rating.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_video_rating ADD CONSTRAINT fk_6012faeda76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_rating ADD CONSTRAINT fk_6012faed29c1004e FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_comment_rating ADD CONSTRAINT fk_791fa169a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_comment_rating ADD CONSTRAINT fk_791fa169f8697d13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
