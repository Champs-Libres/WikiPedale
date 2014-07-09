<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140624102258 extends AbstractMigration
{
   public function up(Schema $schema)
   {
      // this up() migration is auto-generated, please modify it to your needs
      $this->addSql("ALTER TABLE place ADD COLUMN salt VARCHAR(255) NOT NULL DEFAULT md5(random()::text);");
   }

   public function down(Schema $schema)
   {
      // this down() migration is auto-generated, please modify it to your needs
      $this->addSql("ALTER TABLE place DROP COLUMN salt;");
   }
}
