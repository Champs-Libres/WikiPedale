<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Draw on map feature
 */
class Version20141021095246 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE place ADD COLUMN drawnGeoJSON TEXT NOT NULL DEFAULT '{\"type\":\"FeatureCollection\",\"features\":[]}'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE place DROP COLUMN drawnGeoJSON;");
    }
}