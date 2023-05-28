<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230424165611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE genre_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movie_person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('CREATE TABLE video (id UUID NOT NULL, created_by_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, snapshot_path VARCHAR(255) NOT NULL, video_path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7CC7DA2CB03A8386 ON video (created_by_id)');
        $this->addSql('COMMENT ON COLUMN video.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN video.created_by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN video.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE video_tag (video_id UUID NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(video_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_F910728729C1004E ON video_tag (video_id)');
        $this->addSql('CREATE INDEX IDX_F9107287BAD26311 ON video_tag (tag_id)');
        $this->addSql('COMMENT ON COLUMN video_tag.video_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2CB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video_tag ADD CONSTRAINT FK_F910728729C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video_tag ADD CONSTRAINT FK_F9107287BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_genre DROP CONSTRAINT fk_2f662c3c217bbb47');
        $this->addSql('ALTER TABLE person_genre DROP CONSTRAINT fk_2f662c3c4296d31f');
        $this->addSql('ALTER TABLE genre DROP CONSTRAINT fk_835033f8dd5b7fce');
        $this->addSql('ALTER TABLE movie_genre DROP CONSTRAINT fk_fd1229648f93b6fc');
        $this->addSql('ALTER TABLE movie_genre DROP CONSTRAINT fk_fd1229644296d31f');
        $this->addSql('ALTER TABLE movie_person DROP CONSTRAINT fk_cd1b4c038f93b6fc');
        $this->addSql('ALTER TABLE movie_person DROP CONSTRAINT fk_cd1b4c03217bbb47');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_genre');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE movie_genre');
        $this->addSql('DROP TABLE movie_person');
        $this->addSql('DROP TABLE movie');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE genre_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movie_person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, surname VARCHAR(255) DEFAULT NULL, pseudo VARCHAR(255) DEFAULT NULL, birth DATE DEFAULT NULL, death DATE DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person_genre (person_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(person_id, genre_id))');
        $this->addSql('CREATE INDEX idx_2f662c3c217bbb47 ON person_genre (person_id)');
        $this->addSql('CREATE INDEX idx_2f662c3c4296d31f ON person_genre (genre_id)');
        $this->addSql('CREATE TABLE genre (id INT NOT NULL, parent_genre_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_835033f8dd5b7fce ON genre (parent_genre_id)');
        $this->addSql('CREATE TABLE movie_genre (movie_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(movie_id, genre_id))');
        $this->addSql('CREATE INDEX idx_fd1229648f93b6fc ON movie_genre (movie_id)');
        $this->addSql('CREATE INDEX idx_fd1229644296d31f ON movie_genre (genre_id)');
        $this->addSql('CREATE TABLE movie_person (id INT NOT NULL, movie_id INT NOT NULL, person_id INT DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_cd1b4c038f93b6fc ON movie_person (movie_id)');
        $this->addSql('CREATE INDEX idx_cd1b4c03217bbb47 ON movie_person (person_id)');
        $this->addSql('CREATE TABLE movie (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, year INT DEFAULT NULL, snapshot_path VARCHAR(255) DEFAULT NULL, movie_path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN movie.added_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE person_genre ADD CONSTRAINT fk_2f662c3c217bbb47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person_genre ADD CONSTRAINT fk_2f662c3c4296d31f FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE genre ADD CONSTRAINT fk_835033f8dd5b7fce FOREIGN KEY (parent_genre_id) REFERENCES genre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT fk_fd1229648f93b6fc FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_genre ADD CONSTRAINT fk_fd1229644296d31f FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_person ADD CONSTRAINT fk_cd1b4c038f93b6fc FOREIGN KEY (movie_id) REFERENCES movie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movie_person ADD CONSTRAINT fk_cd1b4c03217bbb47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE video DROP CONSTRAINT FK_7CC7DA2CB03A8386');
        $this->addSql('ALTER TABLE video_tag DROP CONSTRAINT FK_F910728729C1004E');
        $this->addSql('ALTER TABLE video_tag DROP CONSTRAINT FK_F9107287BAD26311');
        $this->addSql('DROP TABLE video');
        $this->addSql('DROP TABLE video_tag');
    }
}
