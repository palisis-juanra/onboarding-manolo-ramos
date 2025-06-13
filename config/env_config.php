<?php
// Include environment variables configuration 
use Dotenv\Dotenv;
	
// Initialize Dotenvt
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Retrieve enviroment variables from .env

// TourCMS API
$env_tcms_api_url = $_ENV['TCMS_API_URL'];
$env_tcms_api_key = $_ENV['TCMS_API_KEY'];
$env_tcms_marketplace_id = $_ENV['TCMS_MARKETPLACE_ID'];
$env_tcms_channel_id = $_ENV['TCMS_CHANNEL_ID'];
$env_tcms_vendor_id = $_ENV['TCMS_VENDOR_ID'];
$env_tcms_timeout = $_ENV['TCMS_TIMEOUT'];
$env_tcms_result_type = $_ENV['TCMS_RESULT_TYPE'];