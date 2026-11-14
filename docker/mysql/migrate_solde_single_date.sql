-- Table `solde` : une seule date visible (plan_date) + created_at (non affichée).
-- A exécuter UNE fois sur la base existante, après sauvegarde.

-- 1. Reprendre la date réelle de l'opération dans plan_date
--    (priorité : plan_date existant, puis date_transfert, puis date de création)
UPDATE `solde`
   SET `plan_date` = COALESCE(`plan_date`, `date_transfert`, DATE(`created_at`));

-- 2. Supprimer l'ancienne colonne
ALTER TABLE `solde` DROP COLUMN `date_transfert`;
