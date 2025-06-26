<?php
// Include environment variables configuration 
use Dotenv\Dotenv;
	
// Initialize Dotenvt
$dotEnv = Dotenv::createImmutable(dirname(__DIR__));
$dotEnv->load();

// Retrieve enviroment variables from .env

// Project
$envProjectBaseURL = $_ENV['BASE_URL'] ?? '';

// TourCMS API
$envTCMSapiUrl = $_ENV['TCMS_API_URL'];
$envTCMSapiKey = $_ENV['TCMS_API_KEY'];
$envTCMSmarketplaceID = $_ENV['TCMS_MARKETPLACE_ID'];
$envTCMSchannelID = $_ENV['TCMS_CHANNEL_ID'];
$envTCMSvendorID = $_ENV['TCMS_VENDOR_ID'];
$envTCMStimeout = $_ENV['TCMS_TIMEOUT'];
$envTCMSresultType = $_ENV['TCMS_RESULT_TYPE'];

// Redis Client 
$redisHost = $_ENV['REDIS_HOST'];
$redisPort = $_ENV['REDIS_PORT'];
$redisPassword = $_ENV['REDIS_PASSWORD'];

return [
	'project' => [
		'BASE_URL' => $envProjectBaseURL
	],
	'tourcms' => [
		'TCMS_API_URL' => $envTCMSapiUrl,
		'TCMS_API_KEY' => $envTCMSapiKey,
		'TCMS_MARKETPLACE_ID' => $envTCMSmarketplaceID,
		'TCMS_CHANNEL_ID' => $envTCMSchannelID,
		'TCMS_VENDOR_ID' => $envTCMSvendorID,
		'TCMS_TIMEOUT' => $envTCMStimeout,
		'TCMS_RESULT_TYPE' => $envTCMSresultType,
	],
	'redis' => [
		'REDIS_HOST' => $redisHost,
		'REDIS_PORT' => $redisPort,
		'REDIS_PASSWORD' => $redisPassword
	]
];