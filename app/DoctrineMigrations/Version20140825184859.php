<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add moderator to reports
 */
class Version20140825184859 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE place ADD moderator_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE place ADD CONSTRAINT "
              . "FK_741D53CDD0AFA354 FOREIGN KEY (moderator_id) "
              . "REFERENCES group_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_741D53CDD0AFA354 ON place (moderator_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE place DROP CONSTRAINT FK_741D53CDD0AFA354");
        $this->addSql("DROP INDEX IDX_741D53CDD0AFA354");
        $this->addSql("ALTER TABLE place DROP moderator_id");
    }
}
