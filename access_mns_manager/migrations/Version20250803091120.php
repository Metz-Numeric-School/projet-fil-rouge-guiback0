<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250803091120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add the organisation_id column as nullable first
        $this->addSql('ALTER TABLE zone ADD organisation_id INT DEFAULT NULL');
        
        // Update existing zones to assign them to the first organization
        // (This is a temporary assignment - real data should be handled more carefully)
        $this->addSql('UPDATE zone SET organisation_id = (SELECT id FROM organisation LIMIT 1) WHERE organisation_id IS NULL');
        
        // Now make the column NOT NULL
        $this->addSql('ALTER TABLE zone ALTER COLUMN organisation_id SET NOT NULL');
        
        // Add the foreign key constraint
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC0079E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        // Create the index
        $this->addSql('CREATE INDEX IDX_A0EBC0079E6B1585 ON zone (organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE zone DROP CONSTRAINT FK_A0EBC0079E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_A0EBC0079E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE zone DROP organisation_id
        SQL);
    }
}
