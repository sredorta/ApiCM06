<?php

if(env('APP_ENV') === 'testing')
    return['language_test' => 'francais'];
return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'   => 'Ces identifiants ne correspondent pas à nos enregistrements',
    'throttle' => 'Tentatives de connexion trop nombreuses. Veuillez essayer de nouveau dans :seconds secondes.',
    'expired'  => 'Votre session a expiré',
    'invalid'  => 'Votre session n\'est pas valide',
    'error'    => 'Problème de session',
    'login_required' => 'Vous devez être connecté pour exécuter cette operation',
    'admin_required' => 'Vous devez être connecte en tant qu\'administrateur pour exécuter cette opération',
    'member_required' => 'Vous devez être connecté en tant que membre pour exécuter cette opération',
    'already_loggedin' => 'Vous ne pouvez pas être déjà connecté pour exécuter cette opération',
    'user_already_exists' => 'Mobile ou email déjà enregistré dans le système',
    'user_not_found' => "L'utilisateur demandé n'est pas enregistré dans la base de données",
    'signup_success' => 'Création du compte réussie. Validez votre compte email et connectez-vous',
    'login_validate' => "Vous devez valider votre compte email pour pouvoir accéder",
    'account_missing' => "Compte pas trouvé",
    'token_error' => "Le token d'identification n'a pas pu être généré",
    'email_failed' => "Cette adresse email ne correspond pas à nos enregistrements",
    'reset_success' => "Un nouveau mot de passe vous à été envoyé par email",
    'update_success' => "Vos modifications sont bien prises en compte",
    'update_phone_found' => "Ce numéro de téléphone est déjà enregistré dans le système",
    'update_password' => "Votre ancien mot de passe est incorrecte",
    'update_email' => "Cet email est déjà enregistré dans le système",
    'language_unsupported' => "Cette langue n'est pas supporté",
    'account_not_available' => "Ce type de compte n'est pas supporté",
    'account_already'   => "L'utilisateur il à déjà ce compte",
    'account_not_found' => "L'utilisateur n'a pas ce compte",
    'account_toggle' => "Le compte de l'utilisateur n'a pas pu être changé, vérifier les comptes de l'utilisateur",
    'delete_self'    => "Vous ne pouvez pas supprimer votre propre compte depuis l'interface administrateur",
    'delete_last_admin' => "Vous êtes le dernier administrateur, ce compte ne peut pas être supprimer",
    'test' => 'test en français. Bien fait :param'
];
