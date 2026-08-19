<?php

return [
    // Diagram/thumbnail images live in the shared YPIC library and are served
    // from a CDN mirror rather than duplicated in this site's own storage.
    'image_base_url' => env('YAMAHA_PARTS_IMAGE_BASE_URL', 'https://yamahaparts.b-cdn.net/storage/images/'),

    // Star Yamaha has no import pipeline of its own — the yamaha_parts_*
    // tables are pulled from NorthStar Yamaha's already-imported YPIC
    // catalogue database over a restricted SSH connection (forced command,
    // single-purpose key) rather than re-running the ISO/mdbtools import here.
    'catalogue_sync' => [
        'host'     => env('PARTS_SYNC_SSH_HOST', '203.4.149.29'),
        'port'     => env('PARTS_SYNC_SSH_PORT', 22),
        'user'     => env('PARTS_SYNC_SSH_USER', 'northstar'),
        'key_path' => env('PARTS_SYNC_SSH_KEY', '/home/iwcdigit/.ssh/parts_sync_ed25519'),
    ],
];
