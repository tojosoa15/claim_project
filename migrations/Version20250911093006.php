<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911093006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents CHANGE path path VARCHAR(255) NOT NULL, CHANGE name attachements VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE part_detail CHANGE deleted_at deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE picture_of_damage_car CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE vehicle_information CHANGE chasisi_no chasisi_no VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle_information CHANGE chasisi_no chasisi_no VARCHAR(250) DEFAULT NULL');
        $this->addSql('ALTER TABLE documents CHANGE path path VARCHAR(50) NOT NULL, CHANGE attachements name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE part_detail CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE picture_of_damage_car CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
    }
}
