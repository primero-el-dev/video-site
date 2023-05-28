<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230513201140 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, user_id UUID NOT NULL, action VARCHAR(255) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, subject_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (user_id)');
        $this->addSql('COMMENT ON COLUMN notification.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE notification_user_subject_action (notification_id INT NOT NULL, user_subject_action_id INT NOT NULL, PRIMARY KEY(notification_id, user_subject_action_id))');
        $this->addSql('CREATE INDEX IDX_EE58F1F9EF1A9D84 ON notification_user_subject_action (notification_id)');
        $this->addSql('CREATE INDEX IDX_EE58F1F9267CDF34 ON notification_user_subject_action (user_subject_action_id)');
        $this->addSql('CREATE TABLE notification_for_comment_comment (notification_for_comment_id INT NOT NULL, comment_id UUID NOT NULL, PRIMARY KEY(notification_for_comment_id, comment_id))');
        $this->addSql('CREATE INDEX IDX_310B9D1C561D5901 ON notification_for_comment_comment (notification_for_comment_id)');
        $this->addSql('CREATE INDEX IDX_310B9D1CF8697D13 ON notification_for_comment_comment (comment_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_comment_comment.comment_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE notification_for_video_video (notification_for_video_id INT NOT NULL, video_id UUID NOT NULL, PRIMARY KEY(notification_for_video_id, video_id))');
        $this->addSql('CREATE INDEX IDX_B2D6E26D2C8369 ON notification_for_video_video (notification_for_video_id)');
        $this->addSql('CREATE INDEX IDX_B2D6E229C1004E ON notification_for_video_video (video_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_video_video.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE notification_for_user_user (notification_for_user_id INT NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(notification_for_user_id, user_id))');
        $this->addSql('CREATE INDEX IDX_525CC2684EC087F9 ON notification_for_user_user (notification_for_user_id)');
        $this->addSql('CREATE INDEX IDX_525CC268A76ED395 ON notification_for_user_user (user_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_user_user.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_user_subject_action ADD CONSTRAINT FK_EE58F1F9EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_user_subject_action ADD CONSTRAINT FK_EE58F1F9267CDF34 FOREIGN KEY (user_subject_action_id) REFERENCES user_subject_action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_comment_comment ADD CONSTRAINT FK_310B9D1C561D5901 FOREIGN KEY (notification_for_comment_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_comment_comment ADD CONSTRAINT FK_310B9D1CF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_video_video ADD CONSTRAINT FK_B2D6E26D2C8369 FOREIGN KEY (notification_for_video_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_video_video ADD CONSTRAINT FK_B2D6E229C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_user_user ADD CONSTRAINT FK_525CC2684EC087F9 FOREIGN KEY (notification_for_user_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_user_user ADD CONSTRAINT FK_525CC268A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification_user_subject_action DROP CONSTRAINT FK_EE58F1F9EF1A9D84');
        $this->addSql('ALTER TABLE notification_user_subject_action DROP CONSTRAINT FK_EE58F1F9267CDF34');
        $this->addSql('ALTER TABLE notification_for_comment_comment DROP CONSTRAINT FK_310B9D1C561D5901');
        $this->addSql('ALTER TABLE notification_for_comment_comment DROP CONSTRAINT FK_310B9D1CF8697D13');
        $this->addSql('ALTER TABLE notification_for_video_video DROP CONSTRAINT FK_B2D6E26D2C8369');
        $this->addSql('ALTER TABLE notification_for_video_video DROP CONSTRAINT FK_B2D6E229C1004E');
        $this->addSql('ALTER TABLE notification_for_user_user DROP CONSTRAINT FK_525CC2684EC087F9');
        $this->addSql('ALTER TABLE notification_for_user_user DROP CONSTRAINT FK_525CC268A76ED395');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_user_subject_action');
        $this->addSql('DROP TABLE notification_for_comment_comment');
        $this->addSql('DROP TABLE notification_for_video_video');
        $this->addSql('DROP TABLE notification_for_user_user');
    }
}
