<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808134058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Step 1: Add the new column as nullable
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD nom_acces VARCHAR(255) DEFAULT NULL
        SQL);

        // Step 2: Update existing records with default values based on numero_badgeuse
        $this->addSql(<<<'SQL'
            UPDATE acces SET nom_acces = CONCAT('Accès #', numero_badgeuse) WHERE nom_acces IS NULL
        SQL);

        // Step 3: Make the column NOT NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ALTER COLUMN nom_acces SET NOT NULL
        SQL);

        // Step 4: Remove the old column
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP numero_badgeuse
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        // Step 1: Add back the numero_badgeuse column
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD numero_badgeuse INT DEFAULT NULL
        SQL);

        // Step 2: Extract number from nom_acces and populate numero_badgeuse
        $this->addSql(<<<'SQL'
            UPDATE acces SET numero_badgeuse = CAST(SUBSTRING(nom_acces FROM '#([0-9]+)') AS INTEGER) 
            WHERE nom_acces LIKE 'Accès #%'
        SQL);

        // Step 3: Set default values for any remaining NULL values
        $this->addSql(<<<'SQL'
            UPDATE acces SET numero_badgeuse = id WHERE numero_badgeuse IS NULL
        SQL);

        // Step 4: Make numero_badgeuse NOT NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ALTER COLUMN numero_badgeuse SET NOT NULL
        SQL);

        // Step 5: Drop the nom_acces column
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP nom_acces
        SQL);
    }
}
