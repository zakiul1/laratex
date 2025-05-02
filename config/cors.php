<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    // your Vite port here:
    'allowed_origins' => ['http://localhost:5173'],

    'allowed_headers' => ['*'],

    // ...
];