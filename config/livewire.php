<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Layout & Component Layout
    |--------------------------------------------------------------------------
    |
    | Define the fallback layout for full-page Livewire components.
    | In Filament v5, this avoids defaulting to 'layouts::app' which fails
    | with 'No hint path defined for [layouts]'.
    |
    */

    'layout' => 'filament-panels::components.layout.simple',

    'component_layout' => 'filament-panels::components.layout.simple',

    /*
    |--------------------------------------------------------------------------
    | Payload Guards
    |--------------------------------------------------------------------------
    |
    | These settings protect against malicious or oversized payloads that could
    | cause denial of service. The default Livewire limit is 1024KB (1MB).
    | For large Filament tables, bulk actions, and enterprise forms, we expand
    | this limit to 10MB (configurable via LIVEWIRE_PAYLOAD_MAX_SIZE).
    |
    */

    'payload' => [
        'max_size' => (int) env('LIVEWIRE_PAYLOAD_MAX_SIZE', 10 * 1024 * 1024),   // 10MB - maximum request payload size in bytes
        'max_nesting_depth' => 10,   // Maximum depth of dot-notation property paths
        'max_calls' => 50,           // Maximum method calls per request
        'max_components' => 200,     // Maximum components per batch request
    ],

];
