<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230513214436 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_for_user_user DROP CONSTRAINT fk_525cc2684ec087f9');
        $this->addSql('ALTER TABLE notification_for_user_user DROP CONSTRAINT fk_525cc268a76ed395');
        $this->addSql('ALTER TABLE notification_for_video_video DROP CONSTRAINT fk_b2d6e26d2c8369');
        $this->addSql('ALTER TABLE notification_for_video_video DROP CONSTRAINT fk_b2d6e229c1004e');
        $this->addSql('ALTER TABLE notification_for_comment_comment DROP CONSTRAINT fk_310b9d1c561d5901');
        $this->addSql('ALTER TABLE notification_for_comment_comment DROP CONSTRAINT fk_310b9d1cf8697d13');
        $this->addSql('DROP TABLE notification_for_user_user');
        $this->addSql('DROP TABLE notification_for_video_video');
        $this->addSql('DROP TABLE notification_for_comment_comment');
        $this->addSql('ALTER TABLE notification ADD subject_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN notification.subject_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX IDX_BF5476CA23EDC87 ON notification (subject_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_for_user_user (notification_for_user_id INT NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(notification_for_user_id, user_id))');
        $this->addSql('CREATE INDEX idx_525cc268a76ed395 ON notification_for_user_user (user_id)');
        $this->addSql('CREATE INDEX idx_525cc2684ec087f9 ON notification_for_user_user (notification_for_user_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_user_user.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE notification_for_video_video (notification_for_video_id INT NOT NULL, video_id UUID NOT NULL, PRIMARY KEY(notification_for_video_id, video_id))');
        $this->addSql('CREATE INDEX idx_b2d6e229c1004e ON notification_for_video_video (video_id)');
        $this->addSql('CREATE INDEX idx_b2d6e26d2c8369 ON notification_for_video_video (notification_for_video_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_video_video.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE notification_for_comment_comment (notification_for_comment_id INT NOT NULL, comment_id UUID NOT NULL, PRIMARY KEY(notification_for_comment_id, comment_id))');
        $this->addSql('CREATE INDEX idx_310b9d1cf8697d13 ON notification_for_comment_comment (comment_id)');
        $this->addSql('CREATE INDEX idx_310b9d1c561d5901 ON notification_for_comment_comment (notification_for_comment_id)');
        $this->addSql('COMMENT ON COLUMN notification_for_comment_comment.comment_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE notification_for_user_user ADD CONSTRAINT fk_525cc2684ec087f9 FOREIGN KEY (notification_for_user_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_user_user ADD CONSTRAINT fk_525cc268a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_video_video ADD CONSTRAINT fk_b2d6e26d2c8369 FOREIGN KEY (notification_for_video_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_video_video ADD CONSTRAINT fk_b2d6e229c1004e FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_comment_comment ADD CONSTRAINT fk_310b9d1c561d5901 FOREIGN KEY (notification_for_comment_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_for_comment_comment ADD CONSTRAINT fk_310b9d1cf8697d13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX IDX_BF5476CA23EDC87');
        $this->addSql('ALTER TABLE notification DROP subject_id');
    }
}
