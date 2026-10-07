<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SIPEDAS API Key for Coding Agents and Automated Integrations
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk autentikasi REST API oleh coding agent atau servis eksternal
    | via header 'X-API-KEY' atau 'Authorization: Bearer <key>'.
    |
    */
    'api_key' => env('SIPEDAS_API_KEY'),
];
