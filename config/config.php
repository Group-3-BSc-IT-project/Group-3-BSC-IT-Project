<?php

$dbPort = getenv('DB_PORT');
$appBaseUrl = getenv('APP_BASE_URL');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'msbms');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', $dbPort !== false && $dbPort !== '' ? $dbPort : '3306');
define(
	'BASE_URL',
	$appBaseUrl !== false
		? rtrim($appBaseUrl, '/')
		: (getenv('APP_ENV') === 'production' ? '' : '/Group-3-BSC-IT-Project/public')
);

?>