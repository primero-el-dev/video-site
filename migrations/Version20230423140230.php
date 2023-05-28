<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20230423140230 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE genre_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE genre (id INT NOT NULL, parent_genre_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_835033F8DD5B7FCE ON genre (parent_genre_id)');
        $this->addSql('CREATE TABLE movie (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN movie.added_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE genre ADD CONSTRAINT FK_835033F8DD5B7FCE FOREIGN KEY (parent_genre_id) REFERENCES genre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT fk_64c19c1796a8f92');
        $this->addSql('DROP TABLE category');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE genre_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movie_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, parent_category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_64c19c1796a8f92 ON category (parent_category_id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT fk_64c19c1796a8f92 FOREIGN KEY (parent_category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE genre DROP CONSTRAINT FK_835033F8DD5B7FCE');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE movie');
    }
}
