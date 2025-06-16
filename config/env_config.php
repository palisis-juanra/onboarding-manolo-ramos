<?php
// Include environment variables configuration 
use Dotenv\Dotenv;
	
// Initialize Dotenvt
$dotEnv = Dotenv::createImmutable(dirname(__DIR__));
$dotEnv->load();

// Retrieve enviroment variables from .env

// TourCMS API
$envTCMSapiUrl = $_ENV['TCMS_API_URL'];
$envTCMSapiKey = $_ENV['TCMS_API_KEY'];
$envTCMSmarketplaceID = $_ENV['TCMS_MARKETPLACE_ID'];
$envTCMSchannelID = $_ENV['TCMS_CHANNEL_ID'];
$envTCMSvendorID = $_ENV['TCMS_VENDOR_ID'];
$envTCMStimeout = $_ENV['TCMS_TIMEOUT'];
$envTCMSresultType = $_ENV['TCMS_RESULT_TYPE'];