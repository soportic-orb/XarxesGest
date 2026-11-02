<?php

declare(strict_types=1);

return [

    'layout' => [
        'tagline' => 'Enviat per l\'equip de :brand',
        'manage_notifications' => 'Gestiona les notificacions',
        'signoff' => 'Salutacions cordials,',
        'team' => 'L\'equip de TryPost',
    ],

    'account_disconnected' => [
        'subject' => 'Cal tornar a connectar el teu compte de :platform a :workspace',
        'title' => 'Cal tornar a connectar el teu compte de :platform',
        'preview' => 'Torna a connectar el teu compte de :platform a :workspace per continuar programant publicacions.',
        'heading' => 'Compte desconnectat',
        'intro' => 'El compte següent s\'ha desconnectat del workspace :workspace.',
        'reasons_title' => 'Això pot haver passat perquè:',
        'reason_expired' => 'El teu token d\'accés ha caducat',
        'reason_revoked' => 'Has revocat l\'accés a TryPost',
        'reason_error' => 'Hi ha hagut un error d\'autenticació',
        'reconnect_cta' => 'Torna a connectar el teu compte per continuar programant i publicant publicacions.',
        'button' => 'Torna a connectar el compte',
    ],

    'email_verification' => [
        'subject' => 'Verifica la teva adreça electrònica',
        'preview' => 'Verifica la teva adreça electrònica.',
        'greeting' => 'Hola, :name,',
        'body' => 'Confirma la teva adreça electrònica fent clic al botó següent:',
        'button' => 'Verifica l\'adreça electrònica',
        'ignore' => 'Si no has creat cap compte, pots ignorar aquest correu sense problema.',
    ],

    'password_reset' => [
        'subject' => 'Restableix la teva contrasenya',
        'preview' => 'Restableix la teva contrasenya.',
        'greeting' => 'Hola, :name,',
        'body' => 'Hem rebut una sol·licitud per restablir la teva contrasenya. Fes clic al botó següent per crear-ne una de nova:',
        'button' => 'Restableix la contrasenya',
        'expiry' => 'Aquest enllaç caduca d\'aquí a 60 minuts. Si no has sol·licitat restablir la contrasenya, pots ignorar aquest correu sense problema.',
    ],

    'post_at_risk' => [
        'subject' => '{1} :count publicació està en risc a :workspace|[0,*] :count publicacions estan en risc a :workspace',
        'title' => 'És possible que les publicacions no es publiquin',
        'heading' => 'És possible que les publicacions no es publiquin',
        'intro' => 'Cal tornar a connectar els comptes socials següents del teu workspace :workspace perquè aquestes publicacions programades es puguin publicar:',
        'posts_label' => '{1} :count publicació programada: :times (:timezone)|[0,*] :count publicacions programades: :times (:timezone)',
        'reconnect_cta' => 'Torna a connectar aquests comptes ara per no perdre les teves publicacions programades.',
        'button' => 'Torna a connectar els comptes',
    ],

    'post_note_added' => [
        'subject' => ':author ha afegit una nota a una publicació',
        'title' => 'Nova nota de :author',
        'heading' => 'Nova nota en una publicació',
        'body' => ':author ha afegit una nota a una publicació del workspace :workspace.',
        'post_title' => 'Publicació',
        'post_without_text' => 'Aquesta publicació encara no té text.',
        'button' => 'Mostra la nota',
    ],
    'post_approval_requested' => [
        'subject' => ':name t\'ha demanat que aprovis una publicació',
        'title' => 'Una publicació necessita la teva aprovació',
        'preview' => ':name ha demanat aprovació a :workspace.',
        'heading' => 'Una publicació necessita la teva aprovació',
        'body' => ':name (:email) ha demanat aprovació al workspace :workspace.',
        'channel' => 'Canal',
        'requested_time' => 'Hora sol·licitada',
        'next_queue_slot' => 'Pròxima franja de la cua',
        'as_soon_as_approved' => 'Tan bon punt s\'aprovi',
        'post_without_text' => 'Aquesta publicació encara no té text.',
        'button' => 'Mostra les publicacions pendents d\'aprovació',
    ],

    'post_approved' => [
        'subject' => ':name ha aprovat la teva publicació',
        'title' => 'La teva publicació s\'ha aprovat',
        'preview' => ':name ha aprovat la teva publicació a :workspace.',
        'heading' => 'La teva publicació s\'ha aprovat',
        'body' => ':name ha aprovat la teva publicació al workspace :workspace.',
        'channel' => 'Canal',
        'goes_out' => 'Es publicarà',
        'channel_time' => ':channel: :time',
        'publishing_now' => 'Publicant ara',
        'button' => 'Mostra a la cua',
    ],

    'post_rejected' => [
        'subject' => ':name no ha aprovat la teva publicació',
        'title' => 'La teva publicació no s\'ha aprovat',
        'preview' => ':name ha tornat la teva publicació als esborranys.',
        'heading' => 'La teva publicació no s\'ha aprovat',
        'body' => ':name ha tornat als esborranys la teva publicació del workspace :workspace.',
        'channel' => 'Canal',
        'button' => 'Mostra als esborranys',
    ],

    'post_preview' => [
        'no_text' => 'Publicació sense text',
        'error' => 'Què ha anat malament',
    ],

    'post_publish_failed' => [
        'subject' => 'La teva publicació no s\'ha pogut publicar a :workspace',
        'title' => 'La teva publicació no s\'ha pogut publicar',
        'preview' => 'La teva publicació no s\'ha pogut publicar',
        'heading' => 'La teva publicació no s\'ha pogut publicar',
        'body' => 'No hem pogut publicar la teva publicació a :workspace.',
        'button' => 'Mostra la publicació',
    ],

    'post_published' => [
        'subject' => 'La teva publicació s\'ha publicat a :workspace',
        'title' => 'La teva publicació s\'ha publicat',
        'preview' => 'La teva publicació s\'ha publicat correctament.',
        'heading' => 'La teva publicació s\'ha publicat',
        'body' => 'La teva publicació al workspace :workspace s\'ha publicat correctament.',
        'button' => 'Mostra a la xarxa',
        'open_in_app' => 'Obre a TryPost',
    ],

    'webhook_paused' => [
        'subject' => 'Webhook en pausa: :endpoint',
        'title' => 'Webhook en pausa després de fallades repetides',
        'preview' => 'Hem posat un webhook en pausa després de 5 errors de lliurament consecutius.',
        'heading' => 'Webhook en pausa després de fallades repetides',
        'body' => 'Hem posat un webhook en pausa després de 5 errors de lliurament consecutius.',
        'next_steps' => 'Revisa el punt final i torna a activar-lo des de la pàgina de detalls del webhook.',
        'button' => 'Mostra el webhook',
    ],

    'workspace_connections_disconnected' => [
        'subject' => '{1} Cal tornar a connectar :count compte a :workspace|[0,*] Cal tornar a connectar :count comptes a :workspace',
        'title' => 'Cal tornar a connectar comptes',
        'heading' => 'Cal tornar a connectar comptes',
        'intro' => 'Els comptes socials següents del teu workspace <strong>:workspace</strong> s\'han desconnectat i cal tornar-los a connectar:',
        'reasons_title' => 'Això pot haver passat perquè:',
        'reason_expired' => 'Els tokens d\'accés han caducat',
        'reason_revoked' => 'Has revocat l\'accés a TryPost a la plataforma',
        'reason_changed' => 'La plataforma ha canviat els seus requisits d\'autenticació',
        'reconnect_cta' => 'Torna a connectar aquests comptes per continuar programant i publicant publicacions.',
        'button' => 'Torna a connectar els comptes',
    ],

    'workspace_invite' => [
        'subject' => 'T\'han convidat a unir-te a :workspace',
        'title' => 'T\'han convidat a unir-te a :workspace',
        'preview' => 'T\'han convidat a unir-te a :workspace',
        'heading' => 'T\'han convidat!',
        'intro' => 'T\'han convidat a col·laborar al workspace <strong>:workspace</strong>.',
        'role' => 'T\'han convidat com a <strong>:role</strong>.',
        'roles' => ['admin' => 'Administrador', 'member' => 'Membre', 'needs_approval' => 'Membre (les publicacions necessiten aprovació)'],
        'button' => 'Accepta la invitació',
        'expiry' => 'Aquesta invitació caduca d\'aquí a 7 dies.',
    ],

];
