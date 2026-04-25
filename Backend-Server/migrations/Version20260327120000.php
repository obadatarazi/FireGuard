<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `live_location` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `center_id` INT NOT NULL,
                `source_type` VARCHAR(30) NOT NULL,
                `source_id` INT NOT NULL,
                `longitude` VARCHAR(255) NOT NULL,
                `latitude` VARCHAR(255) NOT NULL,
                `recorded_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                UNIQUE INDEX `uniq_live_location_source` (`source_type`, `source_id`),
                INDEX `idx_live_location_center_recorded_at` (`center_id`, `recorded_at`),
                INDEX `idx_live_location_source_recorded_at` (`source_type`, `source_id`, `recorded_at`),
                PRIMARY KEY(`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `location_history` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `center_id` INT NOT NULL,
                `source_type` VARCHAR(30) NOT NULL,
                `source_id` INT NOT NULL,
                `longitude` VARCHAR(255) NOT NULL,
                `latitude` VARCHAR(255) NOT NULL,
                `recorded_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                INDEX `idx_location_history_source_recorded_at` (`source_type`, `source_id`, `recorded_at`),
                INDEX `idx_location_history_center_recorded_at` (`center_id`, `recorded_at`),
                INDEX `idx_location_history_recorded_at` (`recorded_at`),
                PRIMARY KEY(`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
        );

        $this->addSql('ALTER TABLE `live_location` ADD CONSTRAINT `FK_live_location_center` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE RESTRICT;');
        $this->addSql('ALTER TABLE `location_history` ADD CONSTRAINT `FK_location_history_center` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE RESTRICT;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `live_location` DROP FOREIGN KEY `FK_live_location_center`;');
        $this->addSql('ALTER TABLE `location_history` DROP FOREIGN KEY `FK_location_history_center`;');
        $this->addSql('DROP TABLE IF EXISTS `location_history`;');
        $this->addSql('DROP TABLE IF EXISTS `live_location`;');
    }
}
