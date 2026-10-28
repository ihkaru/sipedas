<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAML 2.0 Configuration for DOKTER-V
    |--------------------------------------------------------------------------
    |
    | Connects DOKTER-V as a Service Provider (SP) to rIDM Identity Provider (IdP).
    |
    */
    'strict' => env('SAML_STRICT', false),
    'debug'  => env('SAML_DEBUG', env('APP_DEBUG', false)),

    'sp' => [
        'entityId' => env('SAML_SP_ENTITY_ID', env('APP_URL', 'https://admin.dvlpid.my.id') . '/saml/metadata'),
        'assertionConsumerService' => [
            'url' => env('SAML_SP_ACS_URL', env('APP_URL', 'https://admin.dvlpid.my.id') . '/saml/acs'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ],
        'singleLogoutService' => [
            'url' => env('SAML_SP_SLS_URL', env('APP_URL', 'https://admin.dvlpid.my.id') . '/saml/sls'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        'x509cert' => '',
        'privateKey' => '',
    ],

    'idp' => [
        'entityId' => env('SAML_IDP_ENTITY_ID', 'https://auth.dvlpid.my.id/t/bps'),
        'singleSignOnService' => [
            'url' => env('SAML_IDP_SSO_URL', 'https://auth.dvlpid.my.id/t/bps/saml/sso'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        'singleLogoutService' => [
            'url' => env('SAML_IDP_SLO_URL', 'https://auth.dvlpid.my.id/t/bps/saml/slo'),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        'x509cert' => env('SAML_IDP_CERT', 'MIICuDCCAaCgAwIBAgIQTz9F2KAD05/VvuwqCt8D0TANBgkqhkiG9w0BAQsFADAYMRYwFAYDVQQDDA1ySURNIFNBTUwgYnBzMB4XDTI2MTAxMTAwMDAwMFoXDTM2MTAxMTAwMDAwMFowGDEWMBQGA1UEAwwNcklETSBTQU1MIGJwczCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALSazZGb7hw+wbDPiNFZ7xBFivSPkl8pAjn0ImyFfGD1bUm0ckv9OE9dHTAxagv/v6+/HjzuYchDv9Gb3zDqOrK4zILcVL3G2ZtJgGQNUW2JFUl7shormLOjBFIArd6VeegFo9ps9ZrZGGfGRXyF2DGmKWYecX+FEbYL2VPnep+1WZ/DB3D1NULsNoBUBFoWB8wJZYjMUzzrR5VBsEuW1Csh6Lobpz4jiKEt2xyi6M+S8qDVy0tnuWQ1HS3f+4KZCaEYuRY4pS2srnedb9n75o1ccPIyxn3fMEtVTPBIP6difstEmJgJQ0JmTw9Rl58M2HZ6DGpoCOxqpI6LzdOQWfUCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAGc2J3kvy5Epwmq4Jrm1n0tIRIgTNrotThgE19CS9jRFlNZazpDETwGaJto6Orrt20d5GEX1WRVLG/d7sdn5t6K2EGJpXQCvwHbR+Z46ufuqrw7JGGsPN7RwB68JUri7fX2kc1FD6sj+i5U3y+xnh2MkCqNv/1ciZhJ14E0f4yO0MFex0Q+ByrMTxGLb5rwwpAb5M4PIUkPMlVnpwyfrf8eCIUJr7Lskehu2lhODjQ+z4H6glW0GYpmk46uNVf7P5/gXlf5e4VMLyTdAUuhOBOK0YCBIXdJx5G7Vb+Dexy9kDB/NlNnjEqSUseOBABE0wt0+GbKGTAmg99rg5K1/YJQ=='),
    ],
];
