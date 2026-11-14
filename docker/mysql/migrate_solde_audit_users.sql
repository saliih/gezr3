-- Table `solde` : utilisateur créateur / dernier modificateur.
-- A exécuter UNE fois sur la base existante.
ALTER TABLE `solde`
    ADD COLUMN `created_by` INT DEFAULT NULL AFTER `locked`,
    ADD COLUMN `updated_by` INT DEFAULT NULL AFTER `created_by`;
