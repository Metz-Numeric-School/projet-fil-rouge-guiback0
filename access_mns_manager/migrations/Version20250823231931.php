<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823231931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP CONSTRAINT fk_d0f43b10fb88e14f
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_d0f43b10fb88e14f
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP utilisateur_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP date_fin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD is_principal BOOLEAN DEFAULT false NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler DROP is_principal
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP is_principal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler ADD is_principal BOOLEAN DEFAULT false NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD utilisateur_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD date_fin TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD CONSTRAINT fk_d0f43b10fb88e14f FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_d0f43b10fb88e14f ON acces (utilisateur_id)
        SQL);
    }
}
