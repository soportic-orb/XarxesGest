<?php

declare(strict_types=1);

return [
    'title' => 'MCP',
    'subtitle' => 'Connecta assistents d\'IA al teu workspace.',
    'copy' => 'Copia',
    'connect' => 'Connecta :client',
    'name_label' => 'Nom',
    'url_label' => 'URL del servidor',
    'config_label' => 'Configuració',
    'tabs' => [
        'connected' => 'Aplicacions connectades',
        'connect' => 'Connecta una aplicació',
    ],
    'connect_other' => 'Connecta',
    'other_hint' => 'Qualsevol aplicació compatible amb MCP.',
    'other_dialog_title' => 'Connecta TryPost a una altra aplicació',
    'connected_description' => 'Assistents amb els quals has iniciat sessió. Pots desconnectar els que ja no utilitzis.',
    'connected_empty_title' => 'Cap aplicació connectada',
    'connected_empty' => 'Connecta Claude, ChatGPT o una altra aplicació per veure-la aquí.',
    'disconnect' => 'Desconnecta',
    'disconnect_title' => 'Desconnecta l\'aplicació',
    'disconnect_confirm' => 'Això tanca la sessió de l\'aplicació a TryPost. Haurà de tornar a connectar-se abans de poder utilitzar MCP de nou.',
    'disconnected' => 'Aplicació desconnectada.',
    'copied' => 'Copiat',
    'last_used' => 'Darrer ús',
    'never' => 'Mai',
    'connector_name' => 'TryPost',

    'authorize' => [
        'page_title' => 'Autoritza l\'aplicació - TryPost',
        'app_title' => 'Autoritza MCP',
        'heading' => 'Autoritza :client',
        'intro' => 'Aquesta aplicació podrà:',
        'intro_capability' => 'Utilitzar les funcions disponibles de MCP.',
        'logged_in_as' => 'Sessió iniciada com a:',
        'workspace' => 'Workspace seleccionat:',
        'workspace_scope' => 'Aquesta connexió només accedirà al workspace seleccionat.',
        'permissions' => 'Permisos:',
        'cancel' => 'Cancel·la',
        'approve' => 'Autoritza',
        'approving' => 'Autoritzant...',
        'select_workspace' => 'Selecciona un workspace',
        'search_workspace' => 'Cerca workspaces...',
        'no_workspace_found' => 'No s\'ha trobat cap workspace',
        'scope_mcp_use' => 'Utilitza el servidor MCP',
        'error_page_title' => 'L\'autorització ha fallat - TryPost',
        'error_title' => 'No s\'ha pogut connectar',
        'error_body' => 'Aquesta sol·licitud d\'autorització no és vàlida o ha caducat. Tanca aquesta finestra i torna a provar de connectar des del teu client MCP.',
        'error_code' => 'Codi d\'error: :error',
    ],

    'other_clients_title' => 'Altres aplicacions',

    'dialog' => [
        'title' => 'Connecta TryPost a :client',
        'description' => 'Porta aproximadament un minut. Aquesta pàgina s\'actualitza en quant et connectis.',
        'open' => 'Obre :client',
        'steps' => [
            'claude' => [
                'paste' => 'Enganxa la URL al camp d\'URL del connector',
                'add' => 'Fes clic a Afegeix i després a Connecta',
                'login' => 'Inicia sessió a TryPost i tria el teu workspace',
            ],
            'chatgpt' => [
                'open' => 'Obre TryPost a ChatGPT i fes clic a Connecta',
                'login' => 'Inicia sessió a TryPost i tria el teu workspace',
            ],
            'other' => [
                'add' => 'Afegeix un servidor MCP personalitzat a la teva aplicació',
                'paste' => 'Enganxa el nom i la URL, o la configuració',
                'login' => 'Inicia sessió a TryPost i tria el teu workspace',
            ],
        ],
        'waiting' => 'Esperant que la teva IA es connecti…',
    ],
    'prompt' => [
        'title' => 'Encara no has connectat cap IA',
        'body' => 'Connecta Claude o ChatGPT per crear, programar i analitzar les teves publicacions només demanant-ho.',
        'not_now' => 'Ara no',
    ],

];
