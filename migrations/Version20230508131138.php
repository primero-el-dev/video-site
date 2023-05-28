<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230508131138 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE user_comment_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_comment_action (id INT NOT NULL, user_id UUID NOT NULL, video_id UUID NOT NULL, action VARCHAR(70) NOT NULL, additional_info JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E65A0BD9A76ED395 ON user_comment_action (user_id)');
        $this->addSql('CREATE INDEX IDX_E65A0BD929C1004E ON user_comment_action (video_id)');
        $this->addSql('COMMENT ON COLUMN user_comment_action.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_comment_action.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT FK_E65A0BD9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_comment_action ADD CONSTRAINT FK_E65A0BD929C1004E FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_comment_action_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT FK_E65A0BD9A76ED395');
        $this->addSql('ALTER TABLE user_comment_action DROP CONSTRAINT FK_E65A0BD929C1004E');
        $this->addSql('DROP TABLE user_comment_action');
    }
}
