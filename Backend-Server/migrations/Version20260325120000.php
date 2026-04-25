<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `mobile_sensor` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `center_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `type` VARCHAR(30) NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `longitude` VARCHAR(255) NOT NULL,
                `latitude` VARCHAR(255) NOT NULL,
                `last_seen_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `center_id` (`center_id`),
                KEY `idx_mobile_sensor_type` (`type`),
                KEY `idx_mobile_sensor_status` (`status`),
                KEY `idx_mobile_sensor_last_seen_at` (`last_seen_at`),
                CONSTRAINT `mobile_sensor_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS `mobile_sensor`;");
    }
}

