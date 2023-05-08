<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230507171144 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE user_video_action_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_video_action (id INT NOT NULL, user_id UUID NOT NULL, video_id UUID NOT NULL, action VARCHAR(70) NOT NULL, additional_info JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FF57505DA76ED395 ON user_video_action (user_id)');
        $this->addSql('CREATE INDEX IDX_FF57505D29C1004E ON user_video_action (video_id)');
        $this->addSql('COMMENT ON COLUMN user_video_action.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_video_action.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_video_action ADD CONSTRAINT FK_FF57505DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video_action ADD CONSTRAINT FK_FF57505D29C1004E FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE user_video_action_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_video_action DROP CONSTRAINT FK_FF57505DA76ED395');
        $this->addSql('ALTER TABLE user_video_action DROP CONSTRAINT FK_FF57505D29C1004E');
        $this->addSql('DROP TABLE user_video_action');
    }
}
