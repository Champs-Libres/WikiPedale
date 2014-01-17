<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140117214156 extends AbstractMigration {

    public function up(Schema $schema) {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");


        $this->addSql("ALTER TABLE zones ADD url VARCHAR(255)");
        $this->addSql("ALTER TABLE zones ADD description TEXT");

        //fill with empty field
        $this->addSql("UPDATE zones set url = '', description ='' ");

        //set description & url not null
        $this->addSql("ALTER TABLE zones ALTER COLUMN description set NOT NULL");
        $this->addSql("ALTER TABLE zones ALTER COLUMN url set NOT NULL");
    }

    public function down(Schema $schema) {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");


        $this->addSql("ALTER TABLE zones DROP url");
        $this->addSql("ALTER TABLE zones DROP description");
    }

}
