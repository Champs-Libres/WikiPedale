<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140403124301 extends AbstractMigration
{
   public function up(Schema $schema)
   {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

      $this->addSql("ALTER TABLE place ADD COLUMN category_id integer;");
      $this->addSql("UPDATE place SET category_id = (SELECT pc.category_id FROM place_category as pc WHERE pc.place_id = place.id LIMIT 1);");
      $this->addSql("ALTER TABLE place ADD CONSTRAINT FK_place_category FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
   }

   public function down(Schema $schema)
   {
      $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

      $this->addSql("ALTER TABLE place DROP CONSTRAINT FK_place_category");
      $this->addSql("ALTER TABLE place DROP COLUMN category_id;");
   }
}
