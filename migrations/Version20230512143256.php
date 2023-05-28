<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230512143256 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_comment_action_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_video_action_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE additional_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_subject_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE additional_data (id INT NOT NULL, content JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_subject_action (id INT NOT NULL, additional_data_id INT DEFAULT NULL, user_id UUID NOT NULL, action VARCHAR(70) NOT NULL, subject_type VARCHAR(255) NOT NULL, subject_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A4509AEC367F72C8 ON user_subject_action (additional_data_id)');
        $this->addSql('CREATE INDEX IDX_A4509AECA76ED395 ON user_subject_action (user_id)');
        $this->addSql('CREATE INDEX IDX_A4509AEC23EDC87 ON user_subject_action (subject_id)');
        $this->addSql('COMMENT ON COLUMN user_subject_action.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_subject_action ADD CONSTRAINT FK_A4509AEC367F72C8 FOREIGN KEY (additional_data_id) REFERENCES additional_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_subject_action ADD CONSTRAINT FK_A4509AECA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_action DROP CONSTRAINT fk_ff57505da76ed395');
        $this->addSql('ALTER TABLE user_video_action DROP CONSTRAINT fk_ff57505d29c1004e');
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT fk_e65a0bd9a76ed395');
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT fk_e65a0bd9f8697d13');
        $this->addSql('DROP TABLE user_video_action');
        $this->addSql('DROP TABLE user_session');
        $this->addSql('DROP TABLE user_comment_action');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE additional_data_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_subject_action_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_comment_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_video_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_video_action (id INT NOT NULL, user_id UUID NOT NULL, video_id UUID NOT NULL, action VARCHAR(70) NOT NULL, additional_info JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ff57505da76ed395 ON user_video_action (user_id)');
        $this->addSql('CREATE INDEX idx_ff57505d29c1004e ON user_video_action (video_id)');
        $this->addSql('COMMENT ON COLUMN user_video_action.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_video_action.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_session (guid VARCHAR(128) NOT NULL, sess_data BYTEA NOT NULL, sess_lifetime INT NOT NULL, sess_time INT NOT NULL, PRIMARY KEY(guid))');
        $this->addSql('CREATE INDEX expiry ON user_session (sess_lifetime)');
        $this->addSql('CREATE TABLE user_comment_action (id INT NOT NULL, user_id UUID NOT NULL, comment_id INT NOT NULL, action VARCHAR(70) NOT NULL, additional_info JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e65a0bd9f8697d13 ON user_comment_action (comment_id)');
        $this->addSql('CREATE INDEX idx_e65a0bd9a76ed395 ON user_comment_action (user_id)');
        $this->addSql('COMMENT ON COLUMN user_comment_action.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_video_action ADD CONSTRAINT fk_ff57505da76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_action ADD CONSTRAINT fk_ff57505d29c1004e FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT fk_e65a0bd9a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT fk_e65a0bd9f8697d13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_subject_action DROP CONSTRAINT FK_A4509AEC367F72C8');
        $this->addSql('ALTER TABLE user_subject_action DROP CONSTRAINT FK_A4509AECA76ED395');
        $this->addSql('DROP TABLE additional_data');
        $this->addSql('DROP TABLE user_subject_action');
    }
}
