-- GEZR Database Schema
-- Toutes les tables de l'application (compatible Symfony + Laravel)

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ----------------------------
-- Table: user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `user` (
    `id`       INT AUTO_INCREMENT NOT NULL,
    `username` VARCHAR(180) NOT NULL,
    `roles`    JSON NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (`username`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: client
-- ----------------------------
CREATE TABLE IF NOT EXISTS `client` (
    `id`          INT AUTO_INCREMENT NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `gtree`       INT DEFAULT NULL,
    `mtree`       INT DEFAULT NULL,
    `otree`       INT DEFAULT NULL,
    `cin`         VARCHAR(8) DEFAULT NULL,
    `num_dossier` VARCHAR(50) DEFAULT NULL,
    `comments`    LONGTEXT DEFAULT NULL,
    `tel`         VARCHAR(50) DEFAULT NULL,
    `ptree`       INT DEFAULT NULL,
    `old_solde`   DOUBLE PRECISION NOT NULL DEFAULT 0,
    `created_at`  DATETIME DEFAULT NULL,
    `updated_at`  DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX uq_num_dossier (`num_dossier`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: vannes
-- ----------------------------
CREATE TABLE IF NOT EXISTS `vannes` (
    `id`         INT AUTO_INCREMENT NOT NULL,
    `reference`  INT NOT NULL,
    `enable`     TINYINT(1) DEFAULT NULL,
    `last_value` INT DEFAULT NULL,
    `link`       VARCHAR(2) DEFAULT NULL,
    `consumed`   TINYINT(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: client_vannes (pivot)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `client_vannes` (
    `client_id` INT NOT NULL,
    `vannes_id` INT NOT NULL,
    PRIMARY KEY (`client_id`, `vannes_id`),
    INDEX IDX_client (`client_id`),
    INDEX IDX_vannes (`vannes_id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: solde
-- ----------------------------
CREATE TABLE IF NOT EXISTS `solde` (
    `id`                INT AUTO_INCREMENT NOT NULL,
    `client_id`         INT DEFAULT NULL,
    `vannes_id`         INT DEFAULT NULL,
    `date_transfert`    DATE DEFAULT NULL,
    `transfert_number`  VARCHAR(50) DEFAULT NULL,
    `amount`            DOUBLE PRECISION DEFAULT NULL,
    `old_counter`       INT DEFAULT NULL,
    `new_counter`       INT DEFAULT NULL,
    `consume`           INT DEFAULT NULL,
    `reminder`          DOUBLE PRECISION DEFAULT NULL,
    `type`              VARCHAR(10) NOT NULL,
    `coupon_number`     VARCHAR(10) DEFAULT NULL,
    `auto_number`       INT DEFAULT NULL,
    `plan_date`         DATE DEFAULT NULL,
    `locked`            TINYINT(1) DEFAULT NULL,
    `created_at`        DATETIME DEFAULT NULL,
    `updated_at`        DATETIME DEFAULT NULL,
    INDEX IDX_client (`client_id`),
    INDEX IDX_vannes (`vannes_id`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: params
-- ----------------------------
CREATE TABLE IF NOT EXISTS `params` (
    `id`            INT AUTO_INCREMENT NOT NULL,
    `unit_price`    DOUBLE PRECISION NOT NULL,
    `valve_per_day` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: prix_m3
-- ----------------------------
CREATE TABLE IF NOT EXISTS `prix_m3` (
    `id`             INT AUTO_INCREMENT NOT NULL,
    `year`           INT NOT NULL,
    `price`          DOUBLE PRECISION NOT NULL,
    `activation_fee` DOUBLE PRECISION DEFAULT NULL,
    UNIQUE INDEX UNIQ_PRIX_M3_YEAR (`year`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Table: client_activation
-- ----------------------------
CREATE TABLE IF NOT EXISTS `client_activation` (
    `id`        INT AUTO_INCREMENT NOT NULL,
    `client_id` INT NOT NULL,
    `year`      INT NOT NULL,
    UNIQUE INDEX uq_client_year (`client_id`, `year`),
    INDEX IDX_client (`client_id`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ----------------------------
-- Foreign keys
-- ----------------------------
ALTER TABLE `client_vannes`
    ADD CONSTRAINT FK_cv_client FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT FK_cv_vannes FOREIGN KEY (`vannes_id`) REFERENCES `vannes` (`id`) ON DELETE CASCADE;

ALTER TABLE `solde`
    ADD CONSTRAINT FK_solde_client FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT FK_solde_vannes FOREIGN KEY (`vannes_id`) REFERENCES `vannes` (`id`) ON DELETE SET NULL;

ALTER TABLE `client_activation`
    ADD CONSTRAINT FK_ca_client FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE;

SET foreign_key_checks = 1;

-- ----------------------------
-- Données initiales
-- ----------------------------
INSERT IGNORE INTO `params` (`unit_price`, `valve_per_day`) VALUES (0.4, 8);
INSERT IGNORE INTO `prix_m3` (`year`, `price`, `activation_fee`) VALUES (2025, 0.3, 30.00);
INSERT IGNORE INTO `prix_m3` (`year`, `price`, `activation_fee`) VALUES (2026, 0.4, 30.00);
