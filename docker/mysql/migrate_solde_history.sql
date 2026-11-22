-- Journal d'audit de la table `solde` (visible par le super admin uniquement).
-- A exécuter UNE fois (ou utiliser `php artisan migrate --force`).
CREATE TABLE IF NOT EXISTS `solde_history` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
    `solde_id`   INT NOT NULL,
    `action`     VARCHAR(10) NOT NULL,
    `user_id`    INT DEFAULT NULL,
    `username`   VARCHAR(180) DEFAULT NULL,
    `client_id`  INT DEFAULT NULL,
    `vannes_id`  INT DEFAULT NULL,
    `type`       VARCHAR(10) DEFAULT NULL,
    `changes`    JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    INDEX IDX_solde (`solde_id`),
    INDEX IDX_user (`user_id`),
    INDEX IDX_client (`client_id`),
    INDEX IDX_created (`created_at`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
