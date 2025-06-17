<?php
// Include Autoloader and environment configuration
require_once __DIR__ . '/config/config.php';
$envConfig = require_once __DIR__ . '/config/env_config.php';

use Core\App;

$app = new App($envConfig);
$app->run();
?>