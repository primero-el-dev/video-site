<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423142735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE movie_person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE movie_genre (movie_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(movie_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_FD1229648F93B6FC ON movie_genre (movie_id)');
        $this->addSql('CREATE INDEX IDX_FD1229644296D31F ON movie_genre (genre_id)');
        $this->addSql('CREATE TABLE movie_person (id INT NOT NULL, movie_id INT NOT NULL, person_id INT DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CD1B4C038F93B6FC ON movie_person (movie_id)');
        $this->addSql('CREATE INDEX IDX_CD1B4C03217BBB47 ON movie_person (person_id)');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, surname VARCHAR(255) DEFAULT NULL, pseudo VARCHAR(255) DEFAULT NULL, birth DATE DEFAULT NULL, death DATE DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person_genre (person_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(person_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_2F662C3C217BBB47 ON person_genre (person_id)');
        $this->addSql('CREATE INDEX IDX_2F662C3C4296D31F ON person_genre (genre_id)');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT FK_FD1229648F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT FK_FD1229644296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_person ADD CONSTRAINT FK_CD1B4C038F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_person ADD CONSTRAINT FK_CD1B4C03217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_genre ADD CONSTRAINT FK_2F662C3C217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_genre ADD CONSTRAINT FK_2F662C3C4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie ADD snapshot_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD movie_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE movie RENAME COLUMN created_at TO year');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE movie_person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE movie_genre DROP CONSTRAINT FK_FD1229648F93B6FC');
        $this->addSql('ALTER TABLE movie_genre DROP CONSTRAINT FK_FD1229644296D31F');
        $this->addSql('ALTER TABLE movie_person DROP CONSTRAINT FK_CD1B4C038F93B6FC');
        $this->addSql('ALTER TABLE movie_person DROP CONSTRAINT FK_CD1B4C03217BBB47');
        $this->addSql('ALTER TABLE person_genre DROP CONSTRAINT FK_2F662C3C217BBB47');
        $this->addSql('ALTER TABLE person_genre DROP CONSTRAINT FK_2F662C3C4296D31F');
        $this->addSql('DROP TABLE movie_genre');
        $this->addSql('DROP TABLE movie_person');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_genre');
        $this->addSql('ALTER TABLE movie DROP snapshot_path');
        $this->addSql('ALTER TABLE movie DROP movie_path');
        $this->addSql('ALTER TABLE movie RENAME COLUMN year TO created_at');
    }
}
