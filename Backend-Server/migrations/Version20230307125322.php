<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230307125322 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        //User
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `user` (
                `id` int NOT NULL AUTO_INCREMENT,
                `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
                `password` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
                `active` tinyint(1) NOT NULL,
                `phone_number` VARCHAR(255) NOT NULL,
                `date_of_birth` DATETIME NULL,
                `gender` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                `verified_at` DATETIME DEFAULT NULL,
                `avatar_file_url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_email` (`email`),
                UNIQUE KEY `idx_phone_number` (`phone_number`) USING BTREE,
                KEY `idx_created_at` (`created_at`) USING BTREE,
                KEY `idx_date_of_birth` (`date_of_birth`) USING BTREE,
                KEY `idx_gender` (`gender`) USING BTREE,
                FULLTEXT KEY `idx_ft_email` (`email`, `full_name`)
            ) "
        );
        // password: test123

        //Roles Group
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `roles_group` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `standarad` tinyint(1) NOT NULL,
            `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_name` (`name`),
            UNIQUE KEY `idx_unique_identifier` (`identifier`) USING BTREE,
            KEY `idx_created_at` (`created_at`),
            FULLTEXT KEY `idx_ft_name_roles` (`name`,`roles`)
            ) "
        );

        //User Roles Group
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `user_roles_group` (
                `roles_group_id` int NOT NULL,
                `user_id` int NOT NULL,
                PRIMARY KEY (`roles_group_id`,`user_id`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `user_roles_group_ibfk_1` FOREIGN KEY (`roles_group_id`) REFERENCES `roles_group` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `user_roles_group_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
            ) "
        );
        //reset_password_request

        $this->addSql("CREATE TABLE IF NOT EXISTS `reset_password_request` (
              `id` int NOT NULL AUTO_INCREMENT,
              `user_id` int NOT NULL,
              `selector` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
              `hashed_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
              `requested_at` datetime NOT NULL,
              `expires_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_user_id` (`user_id`),
              CONSTRAINT `reset_password_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) "
        );

        $this->addSql("CREATE TABLE IF NOT EXISTS refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");

        $this->addSql("INSERT INTO `user` (`id`, `full_name`, `email`, `password`, `active`, `phone_number`, `date_of_birth`, `gender`, `created_at`, `updated_at`, `verified_at`, `avatar_file_url`) VALUES
            (1, 'super admin', 'admin@admin.com', '$2a$10\$GfNL9wzU4O27YztIQnSOcuvGzvnhZNKOsBEk9ZtuvUcYRh5nc5NIy', 1, '0911112222', '2000-01-01 00:00:00', 'MALE', '2023-03-12 17:05:17', '2023-03-12 17:05:17', '2000-01-01 00:00:00', NULL);
        ");

        $this->addSql("INSERT INTO `roles_group` (`id`, `name`, `roles`, `standarad`, `identifier`, `created_at`, `updated_at`) VALUES
            (1, 'Super Admin', 'a:11:{i:0;s:13:\"ROLE_USER_ADD\";i:1;s:16:\"ROLE_USER_DELETE\";i:2;s:16:\"ROLE_USER_UPDATE\";i:3;s:14:\"ROLE_USER_LIST\";i:4;s:14:\"ROLE_USER_SHOW\";i:5;s:21:\"ROLE_SYSTEM_ROLE_LIST\";i:6;s:26:\"ROLE_SYSTEM_ROLE_GROUP_ADD\";i:7;s:29:\"ROLE_SYSTEM_ROLE_GROUP_DELETE\";i:8;s:29:\"ROLE_SYSTEM_ROLE_GROUP_UPDATE\";i:9;s:27:\"ROLE_SYSTEM_ROLE_GROUP_LIST\";i:10;s:27:\"ROLE_SYSTEM_ROLE_GROUP_SHOW\";}', 1, 'SUPER_ADMIN', '2023-04-12 15:28:46', '2023-04-12 15:28:46')
        ");

        $this->addSql("INSERT INTO `user_roles_group` (`roles_group_id`, `user_id`) VALUES(1, 1);");
        
        //center
        $this->addSql("CREATE TABLE IF NOT EXISTS `center` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` text NULL,
            `phone_number` VARCHAR(20) NOT NULL,
            `status` VARCHAR(10) NOT NULL,
            `longitude` VARCHAR(255) NOT NULL,
            `latitude` VARCHAR(255) NOT NULL,
            `name_address` VARCHAR(100) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `phone_number` (`phone_number`),
            KEY `status` (`status`),
            FULLTEXT KEY `idx_ft_name` (`name`)
            ) "
        );
        
        //car
        $this->addSql("CREATE TABLE IF NOT EXISTS `car` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `model` varchar(50) NULL,
            `number_plate` VARCHAR(10) NOT NULL,
            `center_id` INT NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `model` (`model`),
            KEY `center_id` (`center_id`),
            UNIQUE KEY `idx_unique_number_plate` (`number_plate`) USING BTREE,
            FULLTEXT KEY `idx_ft_name` (`name`),
            CONSTRAINT `car_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) "
        );

        // forest
        $this->addSql("CREATE TABLE IF NOT EXISTS `forest` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255) NULL,
            `longitude` VARCHAR(255) NOT NULL,
            `latitude` VARCHAR(255) NOT NULL,
            `name_address` VARCHAR(100) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            FULLTEXT KEY `idx_ft_forest_name` (`name`)
            ) "
        );

        //device
        $this->addSql("CREATE TABLE IF NOT EXISTS `device` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` VARCHAR(255) NULL,
            `forest_id` INT NOT NULL,
            `longitude` VARCHAR(255) NOT NULL,
            `latitude` VARCHAR(255) NOT NULL,
            `name_address` VARCHAR(100) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `forest_id` (`forest_id`),
            FULLTEXT KEY `idx_ft_device_name` (`name`),
            UNIQUE KEY `idx_unique_device_name` (`name`) USING BTREE,
            CONSTRAINT `deviec_ibfk_2` FOREIGN KEY (`forest_id`) REFERENCES `forest` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
            ) "
        );

        // fire
        $this->addSql("CREATE TABLE IF NOT EXISTS `fire` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `forest_id` INT NOT NULL,
            `device_id` INT NOT NULL,
            `status` VARCHAR(20) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `forest_id` (`forest_id`),
            KEY `device_id` (`device_id`),
            KEY `status` (`status`),
            CONSTRAINT `fire_ibfk_1` FOREIGN KEY (`forest_id`) REFERENCES `forest` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
            CONSTRAINT `fire_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
        )");

        $this->addSql("CREATE TABLE IF NOT EXISTS `fire_brigade` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(20) NOT NULL,  
            `center_id` INT NOT NULL,
            `number_of_team` INT NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `password` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `center_id` (`center_id`),
            CONSTRAINT `fire_brigade_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
        )");

        $this->addSql("CREATE TABLE IF NOT EXISTS `device_value` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `value_heat` float NOT NULL,
            `value_moisture` float NOT NULL,
            `value_gas` float NOT NULL,
            `date` DATETIME NOT NULL,
            `status` VARCHAR(255) NULL,
            `device_id` INT NOT NULL,
            `created_at` DATETIME NOT NULL, 
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `device_id` (`device_id`),
            CONSTRAINT `device_value_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
        )");

        $this->addSql("CREATE TABLE IF NOT EXISTS `task_fire_brigade` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `fire_brigade_id` INT NOT NULL,
            `fire_id` INT NOT NULL,
            `note` VARCHAR(255) NULL,
            `status` VARCHAR(100) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `fire_brigade_id` (`fire_brigade_id`),
            KEY `fire_id` (`fire_id`),
            CONSTRAINT `task_fire_brigade_ibfk_1` FOREIGN KEY (`fire_brigade_id`) REFERENCES `fire_brigade` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
            CONSTRAINT `task_fire_brigade_ibfk_2` FOREIGN KEY (`fire_id`) REFERENCES `fire` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
        )");
        
        $this->addSql(
            "CREATE TABLE IF NOT EXISTS `emergency_request` (
                `id` int NOT NULL AUTO_INCREMENT,
                `center_id` INT NOT NULL,
                `fire_id` INT NOT NULL,
                `fire_brigade_id` INT NOT NULL,
                `status` VARCHAR(50) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `center_id` (`center_id`),
                KEY `fire_id` (`fire_id`),
                KEY `fire_brigade_id` (`fire_brigade_id`),
                CONSTRAINT `emergency_request_ibfk_1` FOREIGN KEY (`center_id`) REFERENCES `center` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                CONSTRAINT `emergency_request_ibfk_2` FOREIGN KEY (`fire_id`) REFERENCES `fire` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                CONSTRAINT `emergency_request_ibfk_3` FOREIGN KEY (`fire_brigade_id`) REFERENCES `fire_brigade` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

            ) "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE IF EXISTS `user`;");
        $this->addSql("DROP TABLE IF EXISTS `roles`;");
        $this->addSql("DROP TABLE IF EXISTS `roles_group`;");
        $this->addSql("DROP TABLE IF EXISTS `user_roles_group`;");
        $this->addSql("DROP TABLE IF EXISTS `reset_password_request`;");
        $this->addSql("DROP TABLE IF EXISTS `refresh_tokens`;");
        $this->addSql("DROP TABLE IF EXISTS `center`;");
        $this->addSql("DROP TABLE IF EXISTS `car`;");
        $this->addSql("DROP TABLE IF EXISTS `device`;");
        $this->addSql("DROP TABLE IF EXISTS `forest`;");
        $this->addSql("DROP TABLE IF EXISTS `fire`;");
        $this->addSql("DROP TABLE IF EXISTS `fire_brigade`;");
        $this->addSql("DROP TABLE IF EXISTS `device_value`;");
        $this->addSql("DROP TABLE IF EXISTS `task_fire_brigade`;");
    }
}
