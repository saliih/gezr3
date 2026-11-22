-- Commentaire libre sur une ligne de solde. Executer UNE fois (ou php artisan migrate --force).
ALTER TABLE `solde` ADD COLUMN `comment` TEXT DEFAULT NULL;
