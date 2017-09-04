<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170828104401 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team CHANGE short_name short_name VARCHAR(255) DEFAULT NULL, CHANGE code_name code_name VARCHAR(255) DEFAULT NULL, CHANGE market_value market_value VARCHAR(255) DEFAULT NULL, CHANGE crest_url crest_url VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team CHANGE short_name short_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE code_name code_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE market_value market_value VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE crest_url crest_url VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
