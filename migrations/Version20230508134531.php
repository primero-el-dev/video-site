<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508134531 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT fk_e65a0bd929c1004e');
        $this->addSql('DROP INDEX idx_e65a0bd929c1004e');
        $this->addSql('ALTER TABLE user_comment_action ADD comment_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_comment_action DROP video_id');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT FK_E65A0BD9F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E65A0BD9F8697D13 ON user_comment_action (comment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT FK_E65A0BD9F8697D13');
        $this->addSql('DROP INDEX IDX_E65A0BD9F8697D13');
        $this->addSql('ALTER TABLE user_comment_action ADD video_id UUID NOT NULL');
        $this->addSql('ALTER TABLE user_comment_action DROP comment_id');
        $this->addSql('COMMENT ON COLUMN user_comment_action.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT fk_e65a0bd929c1004e FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e65a0bd929c1004e ON user_comment_action (video_id)');
    }
}
