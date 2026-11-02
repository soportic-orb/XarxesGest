<?php

declare(strict_types=1);

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

    'failed' => 'Aquestes credencials no coincideixen amb els nostres registres.',
    'password' => 'La contrasenya proporcionada no és correcta.',
    'throttle' => 'Massa intents d\'inici de sessió. Torna-ho a provar d\'aquí a :seconds segons.',

    'flash' => [
        'welcome' => 'Et donem la benvinguda a TryPost!',
        'welcome_trial' => 'Et donem la benvinguda a TryPost! El teu període de prova ha començat.',
    ],

    'legal' => 'En continuar, acceptes les nostres <a href=":terms_url" target="_blank">Condicions del servei</a> i la nostra <a href=":privacy_url" target="_blank">Política de privacitat</a>.',

    'reviews' => [
        'eyebrow' => '5/5 a G2',
        'heading' => 'Estimat per la gent que publica cada dia',
        'paulo_dantas' => [
            'role' => 'Fundador, chatadv.com.br',
            'quote' => 'La simplicitat de crear, organitzar i distribuir contingut per totes les xarxes socials. Amb MCP, podem fer servir la IA que preferim, com Claude o ChatGPT, per crear contingut i programar-lo des d\'allà.',
        ],
        'diego' => [
            'role' => 'CEO, Globalfy.com',
            'quote' => 'M\'agrada com de fàcil és fer servir TryPost. Puc crear una publicació directament a Claude, després utilitzar el MCP per publicar-la i programar-la per al futur. Ho vaig configurar en cinc minuts.',
        ],
        'luiz' => [
            'role' => 'Creador de contingut',
            'quote' => 'M\'encanta com de fàcil és connectar les meves eines i agents d\'IA i programar les meves publicacions en 9 plataformes de xarxes socials en només uns minuts.',
        ],
        'pedro' => [
            'role' => 'Fundador, templated.io',
            'quote' => 'Molt fàcil de fer servir i d\'integrar. Amb el MCP només necessito la interfície per connectar els comptes de xarxes socials.',
        ],
        'paulo_castellano' => [
            'role' => 'Fundador, changelogfy.com',
            'quote' => 'M\'encanta la integració amb MCP perquè em permet organitzar tots els meus comptes de xarxes socials des de Claude o ChatGPT.',
        ],
    ],

    'or_continue_with' => 'O continua amb',
    'or_continue_with_email' => 'O continua amb el correu electrònic',
    'google_login' => 'Inicia la sessió amb Google',
    'google_signup' => 'Registra\'t amb Google',
    'github_login' => 'Inicia la sessió amb GitHub',
    'github_signup' => 'Registra\'t amb GitHub',
    'github_email_unavailable' => 'No s\'ha pogut obtenir el teu correu electrònic de GitHub. Fes públic el teu correu de GitHub o concedeix l\'abast de correu electrònic i torna-ho a provar.',

    'login' => [
        'title' => 'Inicia la sessió a TryPost',
        'page_title' => 'Inicia la sessió',
        'email' => 'Adreça electrònica',
        'password' => 'Contrasenya',
        'show_password' => 'Mostra la contrasenya',
        'hide_password' => 'Amaga la contrasenya',
        'forgot_password' => 'Has oblidat la contrasenya?',
        'remember_me' => 'Recorda\'m',
        'submit' => 'Inicia la sessió',
        'no_account' => 'No tens un compte?',
        'sign_up' => 'Registra\'t',
    ],

    'register' => [
        'title' => 'Crea un compte de TryPost',
        'page_title' => 'Registre',
        'name' => 'Nom',
        'name_placeholder' => 'Nom complet',
        'email' => 'Adreça electrònica',
        'password' => 'Contrasenya',
        'show_password' => 'Mostra la contrasenya',
        'hide_password' => 'Amaga la contrasenya',
        'submit' => 'Crea el compte',
        'has_account' => 'Ja tens un compte?',
        'log_in' => 'Inicia la sessió',
        'password_requirements' => [
            'min' => 'Almenys :count caràcters',
            'mixed_case' => 'Lletres majúscules i minúscules',
            'number' => 'Almenys 1 número',
            'symbol' => 'Almenys 1 símbol',
        ],
    ],

    'forgot_password' => [
        'title' => 'Has oblidat la contrasenya',
        'description' => 'Introdueix el teu correu electrònic per rebre un enllaç de restabliment de la contrasenya',
        'page_title' => 'Has oblidat la contrasenya',
        'email' => 'Adreça electrònica',
        'submit' => 'Envia l\'enllaç de restabliment de la contrasenya',
        'return_to' => 'O torna a',
        'log_in' => 'inicia la sessió',
    ],

    'reset_password' => [
        'title' => 'Restableix la contrasenya',
        'description' => 'Introdueix a sota la teva nova contrasenya',
        'page_title' => 'Restableix la contrasenya',
        'email' => 'Correu electrònic',
        'password' => 'Contrasenya',
        'confirm_password' => 'Confirma la contrasenya',
        'confirm_placeholder' => 'Confirma la contrasenya',
        'submit' => 'Restableix la contrasenya',
    ],

    'verify_email' => [
        'title' => 'Verifica el correu electrònic',
        'description' => 'Verifica la teva adreça electrònica fent clic a l\'enllaç que et vam enviar per correu.',
        'page_title' => 'Verificació del correu electrònic',
        'link_sent' => 'S\'ha enviat un nou enllaç de verificació a l\'adreça electrònica que vas indicar en registrar-te.',
        'resend' => 'Torna a enviar el correu de verificació',
        'log_out' => 'Tanca la sessió',
    ],

    'accept_invite' => [
        'page_title' => 'Accepta la invitació',
        'title' => "T'han convidat!",
        'description' => "T'han convidat a unir-te al workspace :workspace.",
        'workspace' => 'Workspace',
        'your_role' => 'El teu rol',
        'email' => 'Correu electrònic',
        'accept' => 'Accepta la invitació',
        'decline' => 'Rebutja la invitació',
        'login_prompt' => 'Inicia la sessió o crea un compte per acceptar aquesta invitació.',
        'log_in' => 'Inicia la sessió',
        'create_account' => 'Crea un compte',
        'expired_title' => 'Aquesta invitació ja no és vàlida',
        'expired_description' => 'El workspace d\'aquesta invitació s\'ha eliminat. Demana al propietari del compte una nova invitació si encara necessites accés.',
        'expired_action' => 'Ves a l\'inici',
    ],

];
