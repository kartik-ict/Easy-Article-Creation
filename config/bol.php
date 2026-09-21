<?php
return [
    'token_url' => env('BOL_TOKEN_URL', 'https://login.bol.com/token'),
    'api_url' => env('BOL_API_URL', 'https://api.bol.com'),
    'client_id' => env('BOL_CLIENT_ID', ''),
    'client_secret' => env('BOL_CLIENT_SECRET', ''),

    // The circular-pilot image-import endpoint is still experimental on Bol's side (confirmed
    // via their own engineer: "the api path will still change"), so its path is a config value
    // rather than hardcoded — swapping it later is a one-line env change, not a code change.
    'offer_import_path' => env('BOL_OFFER_IMPORT_PATH', '/public-api/offers/{offerId}/import'),
    'offer_import_status_path' => env('BOL_OFFER_IMPORT_STATUS_PATH', '/public-api/offers/{offerId}/import/{batchId}'),
];
