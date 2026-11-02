<?php

declare(strict_types=1);

return [
    'title' => 'Reaprofitar',
    'new' => 'Nou reaprofitament',

    'flow' => [
        'no_source' => 'Sense compte d\'origen',
        'no_destinations' => 'Encara sense destinació',
    ],

    'publish_mode' => [
        'title' => 'Publicació',

        'description' => 'Què passa quan apareix una publicació nova.',
    ],

    'publish_modes' => [
        'publish' => 'Publica automàticament',

        'publish_hint' => 'Cada publicació nova es programa en el moment en què es detecta.',

        'draft' => 'Crea com a esborrany',

        'draft_hint' => 'Cada publicació nova es converteix en un esborrany aquí perquè la revisis i la publiquis.',
    ],

    'formats' => [
        'reel' => 'Reels',
        'video' => 'Vídeos',
        'story' => 'Stories',
    ],

    'source' => [
        'title' => 'Origen',
        'description' => 'TryPost vigila aquest compte per detectar publicacions noves del format següent.',
        'watch_label' => 'Vigila',
        'watch_description' => 'El tipus de publicació que inicia una republicació.',
        'needs_reconnect' => 'Cal tornar a connectar',
    ],

    'summary' => [
        'sentence' => 'Cada :format nou que publiquis a :source es republica a :destinations.',
        'no_destinations' => 'Cada :format nou que publiquis a :source està esperant una destinació.',
        'no_source' => 'Aquest reaprofitament no té cap compte d\'origen. Tria\'n un per tornar-lo a iniciar.',
    ],

    'empty' => [
        'title' => 'Encara no hi ha cap reaprofitament configurat',
        'description' => 'TryPost vigila el compte que triïs i republica cada publicació nova a les xarxes que escullis.',
    ],

    'table' => [
        'published' => 'Replicat',
        'last_polled' => 'Última comprovació',
    ],

    'status' => [
        'draft' => 'Esborrany',
        'active' => 'Actiu',
        'paused' => 'En pausa',
        'disabled' => 'Desactivat',
    ],

    'create' => [
        'title' => 'Nou reaprofitament',
        'description' => 'Tria el compte que TryPost ha de vigilar. Les destinacions les triaràs a la pantalla següent.',
        'source_label' => 'Compte d\'origen',
        'source_placeholder' => 'Tria un compte',
        'source_search' => 'Cerca comptes',
        'source_empty' => 'No s\'ha trobat cap compte.',
        'source_placeholder' => 'Selecciona un compte',
        'no_accounts' => 'Connecta primer un compte d\'Instagram o de Facebook. Només aquests poden ser un origen, perquè són les úniques xarxes que ens permeten baixar el vídeo.',
        'submit' => 'Crea',
        'connect' => 'Connecta un compte',
    ],

    'show' => [
        'title' => 'Reaprofitar',
        'saving' => 'Desant...',
        'saved' => 'Desat',
    ],

    'tabs' => [
        'configuration' => 'Configuració',
        'activity' => 'Activitat',
        'settings' => 'Ajustos',
    ],

    'destinations' => [
        'title' => 'Destinacions',
        'description' => 'Tria els comptes que el reben. Cadascun publica en el format que triïs.',
        'hint' => 'Els textos només s\'adapten per xarxa quan superen el límit d\'aquella xarxa.',
        'none_available' => 'Encara no hi ha cap altre compte connectat en aquest workspace.',
        'publish_as' => 'Publica com a',
    ],

    'status_card' => [
        'title' => 'Estat',
        'activate' => 'Activa',
        'pause' => 'Posa en pausa',
        'resume' => 'Reprèn',
        'disable' => 'Desactiva',
        'watermark' => 'Vigilant des de',
        'last_polled' => 'Última comprovació',
        'draft_hint' => 'Tria almenys una destinació i després activa-ho. Només es repliquen les publicacions fetes després d\'activar-ho.',
        'active_hint' => 'TryPost comprova aquest compte regularment i replica cada publicació nova.',
        'paused_hint' => 'Les comprovacions estan en espera. En reprendre, continua des d\'on s\'havia aturat, de manera que no es perd res del que s\'hagi publicat mentrestant.',
        'disabled_hint' => 'Desactivat. Si ho tornes a activar, comença de zero: el que hagis publicat mentre estava desactivat no es replicarà.',
    ],

    'items' => [
        'source' => 'Original',
        'published_at' => 'Publicat',
        'status' => 'Estat',
        'detail' => 'Detall',
        'posts' => 'Replicat a',
        'view_original' => 'Mostra l\'original',
        'original_from' => 'original del :date',
        'empty' => [
            'title' => 'Encara no hi ha res',
            'description' => 'Les publicacions que aquest compte publiqui fora de TryPost apareixeran aquí.',
        ],
        'open_post' => 'Obre la publicació',
        'statuses' => [
            'pending' => 'En cua',
            'processing' => 'Processant',
            'published' => 'Replicat',
            'drafted' => 'Com a esborrany',
            'skipped' => 'Omès',
            'failed' => 'Fallat',
        ],
        'reasons' => [
            'published_via_trypost' => 'Ja publicat a través de TryPost',
            'media_url_missing' => 'La xarxa no ha compartit cap fitxer descarregable, normalment per l\'àudio amb drets d\'autor',
            'download_failed' => 'No s\'ha pogut baixar el vídeo',
            'post_creation_failed' => 'No s\'han pogut crear les publicacions',
            'no_usable_destinations' => 'No hi havia cap destinació disponible on publicar',
        ],
    ],

    'menu' => [
        'label' => 'Més accions',
    ],

    'danger' => [
        'title' => 'Elimina aquest reaprofitament',
        'description' => 'Les comprovacions s\'aturen a l\'instant. Les publicacions ja creades es queden al teu calendari.',
        'delete' => 'Elimina el reaprofitament',
    ],

    'health' => [
        'stopped_itself' => 'S\'ha aturat per si sol — obre\'l per veure el motiu',
        'source_missing' => 'La replicació està en espera: aquest reaprofitament no té cap compte d\'origen. Tria\'n un i després reprèn-lo.',
        'source_unusable' => 'La replicació està en espera: cal tornar a connectar el compte que vigila aquest reaprofitament.',
        'no_destinations' => 'La replicació està en espera: no hi ha cap destinació disponible. Afegeix-ne una i després reprèn-lo.',
        'ready' => 'El problema s\'ha resolt. Reprèn aquest reaprofitament per tornar a replicar.',
    ],

    'errors' => [
        'source_already_used' => 'Aquest compte ja alimenta un altre reaprofitament. Edita aquell en comptes d\'aquest.',
        'source_missing' => 'Tria un compte per vigilar abans d\'iniciar aquest reaprofitament.',
        'source_unusable' => 'Torna a connectar el compte que vigila aquest reaprofitament abans d\'iniciar-lo.',
        'destinations_required' => 'Tria almenys una destinació abans d\'activar-lo.',
        'destination_needs_video' => 'Aquest format no pot contenir un vídeo.',
        'destination_not_supported' => 'Google Business no pot ser una destinació: les seves publicacions no accepten vídeo.',
        'only_paused_resumes' => 'Només es pot reprendre un reaprofitament en pausa.',
        'only_active_pauses' => 'Només es pot posar en pausa un reaprofitament actiu.',
        'only_running_disables' => 'Només es pot desactivar un reaprofitament en marxa.',
        'only_idle_activates' => 'Només es pot activar un reaprofitament en esborrany o desactivat.',
        'destination_unavailable' => 'Aquest compte de destinació ja no està disponible.',
        'destination_is_source' => 'Aquesta destinació és el compte que vigila aquest reaprofitament.',
        'source_unavailable' => 'Aquest compte d\'origen ja no està disponible.',
        'action_failed' => 'Alguna cosa ha anat malament. Revisa el formulari i torna-ho a provar.',
    ],
];
