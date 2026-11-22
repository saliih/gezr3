<?php

namespace App\Support;

/**
 * Modules de l'application et actions que le ROLE_ADMIN peut accorder à chaque utilisateur.
 * Les ROLE_ADMIN / ROLE_SUPER_ADMIN ont toujours tous les droits ; « utilisateurs » et
 * « سجل العمليات » restent réservés aux admins et ne figurent pas dans cette matrice.
 */
class Permissions
{
    public const VIEW   = 'view';
    public const CREATE = 'create';
    public const EDIT   = 'edit';
    public const DELETE = 'delete';

    /** Libellés par défaut des actions */
    public const ACTION_LABELS = [
        self::VIEW   => 'عرض',
        self::CREATE => 'إضافة',
        self::EDIT   => 'تعديل',
        self::DELETE => 'حذف',
    ];

    /**
     * module => [label, route d'atterrissage, actions => libellé]
     */
    public static function modules(): array
    {
        $crud = [
            self::VIEW   => 'عرض',
            self::CREATE => 'إضافة',
            self::EDIT   => 'تعديل',
            self::DELETE => 'حذف',
        ];

        return [
            'dashboard' => ['label' => 'لوحة التحكم',  'route' => 'dashboard',                  'actions' => [self::VIEW => 'عرض']],
            'rapport'   => ['label' => 'الإحصائيات',   'route' => 'rapport.statistiques',       'actions' => [self::VIEW => 'عرض']],
            'clients'   => ['label' => 'الفلاحين',     'route' => 'clients.index',              'actions' => $crud],
            'vannes'    => ['label' => 'العدادات',     'route' => 'vannes.index',               'actions' => $crud],
            'paiements' => ['label' => 'مداخيل',       'route' => 'paiements.index',            'actions' => $crud],
            'consommation' => ['label' => 'الاستهلاك', 'route' => 'consommation.index',         'actions' => $crud],
            'planning'  => ['label' => 'تخطيط',        'route' => 'planning.create',            'actions' => [
                self::VIEW   => 'عرض',
                self::CREATE => 'إضافة تخطيط',
                self::EDIT   => 'تسجيل القراءات والملاحظات',
            ]],
            'activations' => ['label' => 'التفعيلات',  'route' => 'activations.index',          'actions' => $crud],
            'nouvelle-activation' => ['label' => 'تفعيل جديد', 'route' => 'nouvelle-activation.create', 'actions' => [self::CREATE => 'إضافة']],
            'prix-m3'   => ['label' => 'أسعار م³',     'route' => 'prix-m3.index',              'actions' => $crud],
            'params'    => ['label' => 'المعاملات',    'route' => 'params.edit',                'actions' => [self::VIEW => 'عرض', self::EDIT => 'تعديل']],
        ];
    }

    /** Nettoie la saisie du formulaire : ne garde que les modules/actions connus. */
    public static function sanitize(?array $input): array
    {
        $clean = [];
        foreach (self::modules() as $module => $def) {
            $given = array_values(array_intersect(array_keys($def['actions']), (array) ($input[$module] ?? [])));
            if ($given) {
                $clean[$module] = $given;
            }
        }
        return $clean;
    }

    /** Droits proposés par défaut pour un nouvel utilisateur : consultation seule. */
    public static function defaultForNewUser(): array
    {
        $perms = [];
        foreach (self::modules() as $module => $def) {
            if (isset($def['actions'][self::VIEW])) {
                $perms[$module] = [self::VIEW];
            }
        }
        return $perms;
    }

    /** Droits complets (utilisés pour afficher la matrice des anciens utilisateurs sans réglage). */
    public static function all(): array
    {
        $perms = [];
        foreach (self::modules() as $module => $def) {
            $perms[$module] = array_keys($def['actions']);
        }
        return $perms;
    }
}
