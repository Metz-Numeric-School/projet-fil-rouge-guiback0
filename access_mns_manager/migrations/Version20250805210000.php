<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Reset auto-increment sequences to start from 1
 */
final class Version20250805210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reset auto-increment sequences to start from 1';
    }

    public function up(Schema $schema): void
    {
        // Reset sequences to start from 1
        $this->addSql('ALTER SEQUENCE organisation_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE service_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE zone_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE user_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE badge_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE badgeuse_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE acces_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE user_badge_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE travailler_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE service_zone_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE gerer_id_seq RESTART WITH 1');
        $this->addSql('ALTER SEQUENCE pointage_id_seq RESTART WITH 1');
    }

    public function down(Schema $schema): void
    {
        // No need to rollback sequence changes
    }
}