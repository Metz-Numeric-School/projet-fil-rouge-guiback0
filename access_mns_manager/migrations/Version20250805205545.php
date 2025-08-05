<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805205545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove direct organisation_id relationship from zone table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE zone DROP CONSTRAINT fk_a0ebc0079e6b1585
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_a0ebc0079e6b1585
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE zone DROP organisation_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE zone ADD organisation_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE zone ADD CONSTRAINT fk_a0ebc0079e6b1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_a0ebc0079e6b1585 ON zone (organisation_id)
        SQL);
    }
}
