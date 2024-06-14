<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240325161513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, registration_address_id INT NOT NULL, workstation_address_id INT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_4FBF094F7E3C61F9 (owner_id), UNIQUE INDEX UNIQ_4FBF094FC88C665C (registration_address_id), UNIQUE INDEX UNIQ_4FBF094FD5D692FC (workstation_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FC88C665C FOREIGN KEY (registration_address_id) REFERENCES adress (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FD5D692FC FOREIGN KEY (workstation_address_id) REFERENCES adress (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F7E3C61F9');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FC88C665C');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FD5D692FC');
        $this->addSql('DROP TABLE company');
    }
}
