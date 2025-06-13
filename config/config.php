<?php
	require_once './vendor/autoload.php';
	
	use Dotenv\Dotenv;
	
	// Initialize Dotenvt
	$dotenv = Dotenv::createImmutable(dirname(__DIR__));
	$dotenv->load();

	// Retrieve enviroment variables from .env
	$api_url = $_ENV['TCMS_API_URL'];
