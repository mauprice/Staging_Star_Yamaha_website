<?php

return [
    // Diagram/thumbnail images live in the shared YPIC library and are served
    // from a CDN mirror rather than duplicated in this site's own storage.
    'image_base_url' => env('YAMAHA_PARTS_IMAGE_BASE_URL', 'https://yamahaparts.b-cdn.net/storage/images/'),
];
