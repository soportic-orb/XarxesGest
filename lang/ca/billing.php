<?php

declare(strict_types=1);

return [
    'title' => 'Facturació',

    'past_due_notice' => [
        'title' => 'Pagament endarrerit',
        'description' => 'Actualitza el teu mètode de pagament per mantenir activa la subscripció.',
        'cta' => 'Actualitza el pagament',
    ],

    'subscribe' => [
        'billed_monthly' => 'Facturat mensualment',
        'prices' => [
            'socials' => ['monthly' => '$19', 'yearly_per_month' => '$15.83', 'yearly' => '$190'],
            'workspaces' => ['monthly' => '$99', 'yearly_per_month' => '$82.50', 'yearly' => '$990'],
        ],
    ],

    'plans' => [
        'description' => 'Millora o rebaixa el teu pla en qualsevol moment.',
        'monthly' => 'Mensual',
        'yearly' => 'Anual',
        'save_two_months' => '2 mesos gratis',
        'per_month' => '/mes',
        'per_year' => '/any',
        'workspaces_one' => 'Un workspace',
        'workspaces_unlimited' => 'Workspaces il·limitats',
        'workspaces_tooltip' => 'Un workspace és una marca o un client, separat de la resta: amb els seus propis comptes socials, signatures, etiquetes, analítiques, permisos de membres i connexió MCP.',
        'current' => 'Pla actual',
        'switch_to_yearly' => 'Canvia a anual',
        'switch_to_monthly' => 'Canvia a mensual',
        'select' => 'Tria :plan',
        'upgrade' => 'Millora a :plan',
        'downgrade' => 'Rebaixa a :plan',
        'billed_yearly_total' => 'Facturat anualment · :price (2 mesos gratis)',
        'socials_tagline' => 'Ideal per a creadors i marques petites.',
        'workspaces_tagline' => 'Ideal per a agències i empreses més grans.',
        'everything_included' => 'Tot inclòs',
        'features' => [
            'networks_all' => 'Totes les xarxes socials incloses',
            'networks_all_tooltip' => 'Publica a totes des d\'un sol lloc.',
            'accounts_unlimited' => 'Comptes socials il·limitats',
            'accounts_unlimited_tooltip' => 'Connecta tants comptes com vulguis, fins i tot diversos de la mateixa xarxa. Tres Instagrams, per exemple.',
            'calendar' => 'Calendari de contingut',
            'ideas' => 'Planificació de contingut',
            'ideas_tooltip' => 'Anota les teves idees, mantén-les organitzades en un sol lloc i converteix les millors en contingut quan vulguis.',
            'calendar_tooltip' => 'Consulta totes les publicacions programades i publicades en un sol lloc, per mes o per setmana.',
            'ai' => 'Assistent d\'escriptura amb IA',
            'ai_tooltip' => 'Escriu, reescriu i ajusta el to de les teves publicacions amb IA, en el teu idioma.',
            'mcp' => 'Fes servir SocialGest des de Claude o ChatGPT',
            'mcp_tooltip' => 'Connecta Claude, ChatGPT o una altra IA. Demana-li que creï i programi publicacions, ompli la teva cua, organitzi idees i et mostri com estàs creixent.',
            'repurpose' => 'Republicació automàtica de vídeos',
            'repurpose_tooltip' => 'Has publicat un vídeo a Instagram? SocialGest el republica automàticament a TikTok, YouTube, Facebook i les teves altres xarxes.',
            'analytics' => 'Analítiques avançades',
            'analytics_tooltip' => 'Consulta totes les teves xarxes juntes, com el total de seguidors, o filtra per xarxa i per publicació. Visualitzacions, abast, m\'agrada, comentaris i més, per saber què funciona.',
            'team' => 'Membres d\'equip il·limitats',
            'team_tooltip' => 'Convida tanta gent com vulguis al teu equip, sense cost addicional. Defineix què pot fer cada persona i aprova les publicacions abans que surtin.',
        ],
    ],

    'current_plan' => [
        'statuses' => [
            'active' => 'Actiu',
            'trial' => 'Prova gratuïta',
            'cancelling' => 'Cancel·lat',
            'past_due' => 'Endarrerit',
        ],
        'change' => 'Canvia de pla',
        'subscribe' => 'Subscriu-te ara',
        'trial_until' => 'Prova fins al :date',
        'cancelled' => 'Cancel·lat, acaba el :date',
        'discount_until' => ':discount de descompte fins al :date',
        'discount_forever' => ':discount de descompte en cada pagament',
        'discount_once' => ':discount de descompte en el teu pròxim pagament',
    ],

    'subscription' => [
        'title' => 'Mètodes de pagament',
        'default' => 'Per defecte',
        'description' => 'Actualitza la teva targeta o les dades de facturació a Stripe.',
        'no_payment_method' => 'Encara no hi ha cap mètode de pagament registrat.',
        'expires_on' => 'Caduca el :month/:year',
        'manage_stripe' => 'Gestiona a Stripe',
    ],

    'tabs' => [
        'plan' => 'Pla',
        'invoices' => 'Factures',
    ],

    'invoices' => [
        'columns' => [
            'date' => 'Data',
            'amount' => 'Import',
            'status' => 'Estat',
        ],
        'empty_title' => 'Encara no hi ha cap factura',
        'empty_description' => 'Les teves factures apareixeran aquí després del primer pagament.',
        'paid' => 'Pagada',
        'download' => 'Descarrega la factura',
    ],

    'flash' => [
        'plan_changed' => 'Ara tens el pla :plan.',
        'cannot_manage' => 'Només el propietari del compte pot gestionar la facturació.',
        'too_many_workspaces' => 'Tens :count workspaces. Aquest pla en inclou :limit — elimina els sobrants abans de canviar.',
        'subscription_required' => 'Cal una subscripció activa per fer servir les funcions d\'IA.',
    ],

    'processing' => [
        'page_title' => 'Processant...',
        'title' => 'Processant la teva subscripció',
        'description' => 'Espera mentre configurem el teu compte. Només trigarà un moment.',
    ],
];
