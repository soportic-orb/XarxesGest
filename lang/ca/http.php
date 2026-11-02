<?php

declare(strict_types=1);

return [
    'errors' => [
        'unreachable' => 'No hem pogut accedir a aquest lloc web (:reason).',
        'http_status' => 'El lloc web ha retornat un estat inesperat (:status).',
        'invalid_scheme' => 'Només s\'admeten URL http i https.',
        'missing_host' => 'A la URL li falta el host.',
        'unresolvable_host' => 'No hem pogut resoldre el host (:host).',
        'private_network' => 'No es permeten URL que apuntin a xarxes privades.',
    ],
];
