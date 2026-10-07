<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dokter V API Key for Coding Agents and Automated Integrations
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk autentikasi REST API oleh coding agent atau servis eksternal
    | via header 'X-API-KEY' atau 'Authorization: Bearer <key>'.
    | Mendukung fallback ke SIPEDAS_API_KEY untuk backward compatibility.
    |
    */
    'api_key' => env('DOKTER_V_API_KEY', env('SIPEDAS_API_KEY')),
];
