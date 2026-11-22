-- Droits par utilisateur (modules / actions). Exécuter UNE fois (ou php artisan migrate --force).
-- NULL = ancien compte non réglé : il garde l'accès complet d'avant (sauf pages admin).
ALTER TABLE `user` ADD COLUMN `permissions` JSON DEFAULT NULL;
