<?php

return [
    'wsdl_routing_community' => env('CENTILE_WSDL_ROUTING_COMMUNITY'), // Used for trunks
    'wsdl_enterprise' => env('CENTILE_WSDL_ENTERPRISE'), // Used for centrex
    'admin_username' => env('CENTILE_ADMIN_USERNAME'),
    'admin_password' => env('CENTILE_ADMIN_PASSWORD'),
    'default_gateway' => env('CENTILE_DEFAULT_GATEWAY'),
    'device-models' => [
        'Cisco SPA 112 ATA',
        'Cisco SPA 122 ATA',
        'Gigaset N720 IP PRO',
        'Gigaset N510 IP PRO',
        'Polycom UC VVX 300/310',
        'Polycom UC VVX 400/410',
        'Polycom UC VVX 500',
        'Polycom UC VVX 600',
        'Polycom UC SoundStation IP 5000',
        'Polycom UC SoundStation IP 6000',
        'Polycom UC SoundStation IP 7000',
        'Yealink SIP-T19P',
        'Yealink SIP-T19P E2',
        'Yealink SIP-T20P',
        'Yealink SIP-T21P',
        'Yealink SIP-T22P',
        'Yealink SIP-T23G',
        'Yealink SIP-T23P',
        'Yealink SIP-T26P',
        'Yealink SIP-T27P',
        'Yealink SIP-T28P',
        'Yealink SIP-T29G',
        'Yealink SIP-T32G',
        'Yealink SIP-T38G',
        'Yealink SIP-T41P',
        'Yealink SIP-T42G',
        'Yealink SIP-T46G',
        'Yealink VP530',
    ],
    'trunk' => [
        'password_length' => env('CENTILE_TRUNK_PASSWORD_LENGTH', 18),
    ],
];
