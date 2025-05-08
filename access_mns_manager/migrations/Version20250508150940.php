<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508150940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE acces (id SERIAL NOT NULL, zone_id INT DEFAULT NULL, badgeuse_id INT DEFAULT NULL, numero_badgeuse INT NOT NULL, date_installation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D0F43B109F2C3FAB ON acces (zone_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D0F43B102F50F66D ON acces (badgeuse_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE badge (id SERIAL NOT NULL, numero_badge INT NOT NULL, type_badge VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, date_expiration DATE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE badgeuse (id SERIAL NOT NULL, reference VARCHAR(255) NOT NULL, date_installation DATE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gerer (id SERIAL NOT NULL, manageur_id INT DEFAULT NULL, employe_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_103C68BD392E0886 ON gerer (manageur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_103C68BD1B65292 ON gerer (employe_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE organisation (id SERIAL NOT NULL, nom_organisation VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, site_web VARCHAR(255) DEFAULT NULL, date_creation DATE NOT NULL, siret VARCHAR(14) DEFAULT NULL, ca DOUBLE PRECISION DEFAULT NULL, numero_rue INT DEFAULT NULL, suffix_rue VARCHAR(10) DEFAULT NULL, nom_rue VARCHAR(255) NOT NULL, code_postal VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pointage (id SERIAL NOT NULL, badge_id INT DEFAULT NULL, badgeuse_id INT DEFAULT NULL, heure TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7591B20F7A2C2FC ON pointage (badge_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7591B202F50F66D ON pointage (badgeuse_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE service (id SERIAL NOT NULL, organisation_id INT NOT NULL, nom_service VARCHAR(255) NOT NULL, niveau_service INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E19D9AD29E6B1585 ON service (organisation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE service_zone (id SERIAL NOT NULL, service_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EA874EBED5CA9E6 ON service_zone (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EA874EB9F2C3FAB ON service_zone (zone_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE travailler (id SERIAL NOT NULL, utilisateur_id INT DEFAULT NULL, service_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_90B2DF3DFB88E14F ON travailler (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_90B2DF3DED5CA9E6 ON travailler (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, date_inscription DATE NOT NULL, adresse VARCHAR(255) DEFAULT NULL, horraire TIME(0) WITHOUT TIME ZONE DEFAULT NULL, heure_debut TIME(0) WITHOUT TIME ZONE DEFAULT NULL, jours_semaine_travaille INT DEFAULT NULL, poste VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_badge (id SERIAL NOT NULL, utilisateur_id INT DEFAULT NULL, badge_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C32B345FB88E14F ON user_badge (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C32B345F7A2C2FC ON user_badge (badge_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE zone (id SERIAL NOT NULL, nom_zone VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, capacite INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD CONSTRAINT FK_D0F43B109F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces ADD CONSTRAINT FK_D0F43B102F50F66D FOREIGN KEY (badgeuse_id) REFERENCES badgeuse (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gerer ADD CONSTRAINT FK_103C68BD392E0886 FOREIGN KEY (manageur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gerer ADD CONSTRAINT FK_103C68BD1B65292 FOREIGN KEY (employe_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pointage ADD CONSTRAINT FK_7591B20F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pointage ADD CONSTRAINT FK_7591B202F50F66D FOREIGN KEY (badgeuse_id) REFERENCES badgeuse (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD29E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service_zone ADD CONSTRAINT FK_6EA874EBED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service_zone ADD CONSTRAINT FK_6EA874EB9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler ADD CONSTRAINT FK_90B2DF3DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler ADD CONSTRAINT FK_90B2DF3DED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge ADD CONSTRAINT FK_1C32B345F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP CONSTRAINT FK_D0F43B109F2C3FAB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE acces DROP CONSTRAINT FK_D0F43B102F50F66D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gerer DROP CONSTRAINT FK_103C68BD392E0886
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gerer DROP CONSTRAINT FK_103C68BD1B65292
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pointage DROP CONSTRAINT FK_7591B20F7A2C2FC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pointage DROP CONSTRAINT FK_7591B202F50F66D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP CONSTRAINT FK_E19D9AD29E6B1585
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service_zone DROP CONSTRAINT FK_6EA874EBED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service_zone DROP CONSTRAINT FK_6EA874EB9F2C3FAB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler DROP CONSTRAINT FK_90B2DF3DFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE travailler DROP CONSTRAINT FK_90B2DF3DED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge DROP CONSTRAINT FK_1C32B345FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_badge DROP CONSTRAINT FK_1C32B345F7A2C2FC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE acces
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badgeuse
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gerer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE organisation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pointage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE service_zone
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE travailler
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_badge
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE zone
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
