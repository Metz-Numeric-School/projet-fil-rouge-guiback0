<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * GDPR Compliance Migration - Add tracking fields and remove address from User entity
 */
final class Version20250822120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GDPR compliance fields (date_derniere_connexion, date_derniere_modification, compte_actif, date_suppression_prevue) and remove address field from User entity';
    }

    public function up(Schema $schema): void
    {
        // Add new GDPR compliance columns to user table
        $this->addSql('ALTER TABLE "user" ADD COLUMN date_derniere_connexion TIMESTAMP DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD COLUMN date_derniere_modification TIMESTAMP DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD COLUMN compte_actif BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE "user" ADD COLUMN date_suppression_prevue DATE DEFAULT NULL');
        
        // Remove address field if it exists (GDPR data minimization)
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS adresse');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS numero_rue');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS nom_rue');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS code_postal');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS ville');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS pays');
        
        // Initialize existing users with default GDPR compliance values
        $this->addSql('UPDATE "user" SET compte_actif = true WHERE compte_actif IS NULL');
        $this->addSql('UPDATE "user" SET date_derniere_modification = NOW() WHERE date_derniere_modification IS NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove GDPR compliance columns
        $this->addSql('ALTER TABLE "user" DROP COLUMN date_derniere_connexion');
        $this->addSql('ALTER TABLE "user" DROP COLUMN date_derniere_modification');
        $this->addSql('ALTER TABLE "user" DROP COLUMN compte_actif');
        $this->addSql('ALTER TABLE "user" DROP COLUMN date_suppression_prevue');
        
        // Note: We don't restore address fields in down migration to maintain data protection
        // If address restoration is needed, it should be done manually with proper data handling
    }
}