<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230513203619 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_user_subject_action DROP CONSTRAINT fk_ee58f1f9ef1a9d84');
        $this->addSql('ALTER TABLE notification_user_subject_action DROP CONSTRAINT fk_ee58f1f9267cdf34');
        $this->addSql('DROP TABLE notification_user_subject_action');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_user_subject_action (notification_id INT NOT NULL, user_subject_action_id INT NOT NULL, PRIMARY KEY(notification_id, user_subject_action_id))');
        $this->addSql('CREATE INDEX idx_ee58f1f9267cdf34 ON notification_user_subject_action (user_subject_action_id)');
        $this->addSql('CREATE INDEX idx_ee58f1f9ef1a9d84 ON notification_user_subject_action (notification_id)');
        $this->addSql('ALTER TABLE notification_user_subject_action ADD CONSTRAINT fk_ee58f1f9ef1a9d84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_user_subject_action ADD CONSTRAINT fk_ee58f1f9267cdf34 FOREIGN KEY (user_subject_action_id) REFERENCES user_subject_action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
