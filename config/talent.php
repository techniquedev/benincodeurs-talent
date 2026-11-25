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

    // Pays par défaut des numéros WhatsApp.
    'phone_country' => 'BJ',

    // Complétion minimale (en %) pour qu'un profil validé apparaisse dans l'annuaire.
    'directory_min_completion' => 70,

    // Nombre de profils par page dans l'annuaire.
    'directory_per_page' => 12,

];
