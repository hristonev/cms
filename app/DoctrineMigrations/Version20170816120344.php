<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170816120344 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE league (id INT AUTO_INCREMENT NOT NULL, season_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, caption VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, current_matchday INT DEFAULT NULL, number_of_matchdays INT DEFAULT NULL, number_of_teams INT DEFAULT NULL, number_of_games INT DEFAULT NULL, las_update DATETIME DEFAULT NULL, INDEX IDX_3EB4C3184EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE league ADD CONSTRAINT FK_3EB4C3184EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE league DROP FOREIGN KEY FK_3EB4C3184EC001D1');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE league');
    }
}
