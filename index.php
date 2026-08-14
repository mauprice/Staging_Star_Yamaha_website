<?php
// Shared-hosting entry point: document root is the project root, not public/.
// Fix SCRIPT_NAME so Laravel generates correct URLs without a /public/ prefix.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require __DIR__.'/public/index.php';
