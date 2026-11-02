<?php

declare(strict_types=1);

return [
    'bluesky' => [
        'title' => 'Connecta Bluesky',
        'description' => 'Introdueix les teves credencials per connectar',
        'email' => 'Identificador o correu electrònic',
        'email_placeholder' => 'elteuidentificador.bsky.social',
        'app_password' => 'Contrasenya d\'aplicació',
        'app_password_placeholder' => 'xxxx-xxxx-xxxx-xxxx',
        'app_password_hint' => 'Per seguretat, utilitza una <strong>contrasenya d\'aplicació</strong>. Crea\'n una a <a href="https://bsky.app/settings/app-passwords" target="_blank" class="underline">bsky.app/settings</a>.',
        'submit' => 'Connecta Bluesky',
        'submitting' => 'Connectant...',
        'invalid_credentials' => 'Credencials no vàlides.',
        'connection_error' => 'Error en connectar amb Bluesky. Torna-ho a provar.',
    ],

    'mastodon' => [
        'title' => 'Connecta Mastodon',
        'description' => 'Introdueix la teva instància de Mastodon',
        'instance_url' => 'URL de la instància',
        'instance_placeholder' => 'https://mastodon.social',
        'instance_hint' => 'Per exemple: mastodon.social o techhub.social.',
        'submit' => 'Continua amb Mastodon',
        'submitting' => 'Connectant...',
        'instance_unreachable' => 'No s\'ha pogut connectar amb aquesta instància de Mastodon.',
        'connection_error' => 'Error en connectar amb la instància de Mastodon.',
    ],

    'telegram' => [
        'title' => 'Connecta Telegram',
        'description' => 'Vincula un canal o un grup',
        'steps' => 'Passos',
        'step_admin' => 'Afegeix :bot com a administrador al teu canal o grup de Telegram.',
        'open_bot' => 'Obre a Telegram',
        'step_command' => 'Publica aquesta ordre al canal o grup:',
        'waiting' => 'Esperant que es connecti el canal…',
        'copy_command' => 'Copia l\'ordre',
        'expired' => 'Aquesta ordre ha caducat. Genera\'n una de nova per tornar-ho a provar.',
        'new_command' => 'Genera una ordre nova',
        'error_generic' => 'No s\'ha pogut iniciar la connexió. Torna-ho a provar.',
        'network_taken' => 'Aquest workspace ja té un canal de Telegram connectat. Desconnecta\'l primer.',
        'wrong_chat' => 'Publica l\'ordre al canal que estàs tornant a connectar.',
        'busy' => 'Una altra connexió encara s\'està acabant. Torna a publicar l\'ordre d\'aquí a un moment.',
        'help' => [
            'channel_admins' => 'Afegir administradors a un canal',
            'group_admins' => 'Afegir administradors a un grup',
            'bot_privacy' => 'Què poden llegir els bots als grups',
        ],
    ],

    'instagram_connect' => [
        'title' => 'Com vols connectar el teu compte d\'Instagram?',
        'description' => 'Les funcions depenen del tipus de compte d\'Instagram que tinguis i de la connexió que triïs.',
        'professional_title' => 'Professional',
        'professional_types' => '(Empresa i Creador)',
        'facebook_types' => '(vinculat a una pàgina de Facebook)',
        'badge' => 'Publicació automàtica',
        'features' => [
            'automatic' => [
                'title' => 'Publicació automàtica',
                'description' => 'Tu programes i nosaltres publiquem',
            ],
            'metrics' => [
                'title' => 'Mètriques de les publicacions enviades',
                'description' => 'Consulta el rendiment de les publicacions anteriors',
            ],
            'music' => [
                'title' => 'Música als reels',
                'description' => 'Afegeix una cançó de la biblioteca d\'Instagram',
            ],
        ],
        'connect' => 'Connecta amb Instagram',
        'convert_hint' => 'Instagram et proposarà convertir-te fàcilment a un compte professional si cal.',
        'help' => [
            'account_type' => 'Esbrinar el tipus de compte d\'Instagram',
            'convert' => 'Convertir el teu compte d\'Instagram en un compte professional',
        ],
    ],

    'instagram_facebook_requirements' => [
        'title' => 'Connecta Instagram a través de Facebook',
        'subtitle' => 'Això és el que has de saber 👇',
        'heading' => 'Requisits',
        'items' => [
            'account_type' => [
                'lead' => 'Empresa o Creador',
                'rest' => 'd\'Instagram, no un perfil personal d\'Instagram.',
            ],
            'page' => [
                'lead' => 'Vinculat a una pàgina de Facebook,',
                'rest' => 'no a un perfil de Facebook. Has de vincular Instagram a Facebook a Meta?',
            ],
            'admin' => [
                'lead' => 'Sessió iniciada com a administrador de la pàgina de Facebook',
                'rest' => 'amb «control total».',
            ],
            'permissions' => [
                'lead' => 'Tots els permisos seleccionats per a totes les pàgines i comptes d\'Instagram',
                'rest' => 'en connectar, fins i tot els que no connectaràs a TryPost.',
            ],
        ],
        'learn_how' => 'Descobreix com.',
        'note' => 'La connexió no funcionarà si no es compleix algun d\'aquests requisits.',
        'connect' => 'Connecta a través de Facebook',
    ],

    'flash' => [
        'disconnected_paused_repurposes' => 'Compte desconnectat. :count automatització en pausa.|Compte desconnectat. :count automatitzacions en pausa.',
        'disconnected' => 'Compte desconnectat correctament!',
        'session_expired' => 'La sessió ha caducat. Torna-ho a provar.',
        'workspace_not_found' => 'No s\'ha trobat el workspace.',
        'already_connected' => 'Aquesta plataforma ja està connectada.',
        'no_youtube_channels' => 'No s\'ha trobat cap canal de YouTube. Crea un canal primer.',
    ],

    'connect' => [
        'label' => 'Connecta un canal',
        'close' => 'Tanca',
        'title_single' => 'Connecta el teu compte',
        'title_select' => 'Selecciona comptes',
        'subtitle_single' => 'Tria el compte on TryPost publicarà a :network.',
        'subtitle_select' => 'Tria els comptes on TryPost publicarà a :network.',
        'selected' => ':count seleccionats',
        'select_all' => 'Selecciona-ho tot',
        'connected' => 'Connectat',
        'all_connected' => 'Tots els teus comptes de :network ja estan connectats.',
        'finish' => 'Acaba la connexió',
        'switch' => [
            'button' => 'Canvia de compte',
            'button_short' => 'Canvia',
            'title' => 'Connecta un compte diferent',
            'description' => 'Ves a :site i canvia al compte que vulguis; després fes clic a «Connecta amb :network» a continuació.',
            'connect' => 'Connecta amb :network',
        ],
        'types' => [
            'profile' => 'Perfil',
            'page' => 'Pàgina',
            'channel' => 'Canal',
            'location' => 'Ubicació',
            'server' => 'Servidor',
        ],
        'help' => [
            'label' => 'Necessites ajuda',
            'missing' => 'Et falta un compte? Comprova que el gestiones, torna-ho a provar i permet tots els permisos.',
        ],
        'states' => [
            'cancelled' => [
                'title' => 'Connexió cancel·lada',
                'description' => 'No s\'ha connectat res. Pots tornar-ho a provar quan vulguis.',
            ],
            'missing_permission' => [
                'title' => 'Cal un permís',
                'description' => 'TryPost necessita permís per publicar les teves publicacions. Torna a connectar i deixa aquest permís marcat.',
            ],
            'expired' => [
                'title' => 'Aquesta connexió ha caducat',
                'description' => 'Per la teva seguretat, una connexió caduca al cap de 15 minuts. Torna a començar per connectar el teu compte.',
            ],
            'error' => [
                'title' => 'No s\'ha pogut connectar',
            ],
        ],
        'actions' => [
            'try_again' => 'Torna-ho a provar',
            'back' => 'Enrere',
            'connect_again' => 'Torna a connectar',
            'start_again' => 'Torna a començar',
        ],
        'errors' => [
            'bluesky_email_unconfirmed' => 'Confirma el teu correu electrònic a la configuració de Bluesky i torna a connectar el teu compte. TryPost necessita un correu confirmat per publicar vídeos.',
            'error_connecting' => 'Error en connectar el compte. Torna-ho a provar.',
            'network_taken' => 'Aquest workspace ja té un compte per a aquesta xarxa. Desconnecta\'l primer.',
            'wrong_account' => 'Aquest és un compte diferent. Autoritza el que estàs tornant a connectar.',
            'all_connected' => 'Tots els comptes d\'aquest inici de sessió ja estan connectats.',
            'identity_connected' => 'Aquest compte ja està connectat.',
            'busy' => 'Una altra connexió encara s\'està acabant. Torna-ho a provar d\'aquí a un moment.',
            'session_expired' => 'La sessió ha caducat. Torna-ho a provar.',
            'workspace_not_found' => 'No s\'ha trobat el workspace.',
            'invalid_state' => 'Estat no vàlid. Torna-ho a provar.',
            'failed_to_authenticate' => 'No s\'ha pogut autenticar.',
            'failed_to_get_profile' => 'No s\'ha pogut obtenir el perfil.',
            'page_not_found' => 'No s\'ha trobat la pàgina.',
            'channel_not_found' => 'No s\'ha trobat el canal.',
            'pages_read_incomplete' => 'No hem pogut acabar de llegir les teves pàgines. Torna-ho a provar d\'aquí a un moment.',
            'publish_permission_missing' => 'TryPost necessita permís per publicar les teves publicacions. Torna a connectar i deixa aquest permís marcat.',
            'cancelled' => 'Connexió cancel·lada.',
            'pages_missing_permission' => 'Hem trobat pàgines, però cap on puguis publicar. Necessites un rol a la mateixa pàgina i tots els permisos acceptats.',
            'no_facebook_pages' => 'No s\'ha trobat cap pàgina de Facebook. Has de ser administrador d\'almenys una pàgina.',
            'no_facebook_instagram_pages' => 'No s\'ha trobat cap pàgina de Facebook amb comptes d\'Instagram vinculats.',
            'no_youtube_channels' => 'No s\'ha trobat cap canal de YouTube. Crea un canal primer.',
            'not_linkedin_admin' => 'No ets administrador de cap pàgina de LinkedIn.',
            'no_google_business_locations' => 'No s\'ha trobat cap ubicació del perfil d\'empresa de Google. Verifica primer la teva empresa.',
            'location_not_found' => 'No s\'ha trobat la ubicació.',
        ],
    ],
];
