<?php

return [

    /*
    | Premier administrateur, créé au démarrage s'il n'existe pas encore
    | (commande talent:create-admin). Les valeurs viennent uniquement de l'environnement.
    */
    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
        'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
        'last_name' => env('ADMIN_LAST_NAME', 'Bénin Codeurs'),
    ],

    // Page d'adhésion à la communauté VIP (inscription refusée ou adhésion expirée).
    'vip_join_url' => 'https://communaute.benincodeurs.com',

    // Code de confirmation envoyé par e-mail pendant l'inscription.
    'registration' => [
        'code_ttl_minutes' => 15,
        'code_max_attempts' => 5,
        'code_resend_seconds' => 60,
    ],

    // Pays par défaut des numéros WhatsApp.
    'phone_country' => 'BJ',

    // Complétion minimale (en %) pour qu'un profil validé apparaisse dans l'annuaire.
    'directory_min_completion' => 70,

    // Nombre de profils par page dans l'annuaire.
    'directory_per_page' => 12,

    // Contact WhatsApp de Bénin Codeurs (barre de navigation, pied de page) et réseaux sociaux. Vide : lien masqué.
    'support_whatsapp_url' => env('TALENT_SUPPORT_WHATSAPP_URL') ?: 'https://wa.me/2290159273491',
    'support_whatsapp_number' => env('TALENT_SUPPORT_WHATSAPP_NUMBER') ?: '+229 01 59 27 34 91',
    'social' => [
        'facebook' => env('TALENT_SOCIAL_FACEBOOK') ?: 'https://facebook.com/benincodeurs/',
        'instagram' => env('TALENT_SOCIAL_INSTAGRAM') ?: 'https://www.instagram.com/benincodeurs/',
        'linkedin' => env('TALENT_SOCIAL_LINKEDIN') ?: 'https://www.linkedin.com/company/benincodeurs/',
        'youtube' => env('TALENT_SOCIAL_YOUTUBE'),
        'x' => env('TALENT_SOCIAL_X'),
    ],

];
