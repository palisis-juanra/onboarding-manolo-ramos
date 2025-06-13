<?php
namespace Services;

require_once __DIR__ . '/../../config/env_config.php';

use TourCMS\Utils\TourCMS as TourCMS;

class TourCMSClientService
{
	private static $instance = null;
	private $tourCMS = null;

	/**
	 * TourCMSClientService constructor.
	 *
	 * Initializes the TourCMS client with environment variables.
	 * This constructor is private to enforce the singleton pattern.
	 */
	private function __construct() {
		$this->initTourCMSClient();
	}
	
	/**
	 * Initializes the TourCMS client with environment variables.
	 *
	 * This method sets up the TourCMS client using the environment variables defined in the .env file.
	 * It retrieves the necessary parameters such as marketplace ID, API key, result type, and timeout
	 * to create an instance of the TourCMS client.
	 */
	private function initTourCMSClient() {
		$this->tourCMS = new TourCMS(
			$env_tcms_marketplace_id,
			$env_tcms_api_key,
			$env_tcms_result_type,
			$env_tcms_timeout
		);

		$this->tourCMS->set_base_url($env_tcms_api_url);
	}

	/**
	 * Returns the singleton instance of the TourCMSClientService.
	 *
	 * This method ensures that only one instance of the TourCMSClientService is created
	 * and returns that instance. If the instance does not exist, it creates a new one.
	 *
	 * @return TourCMSClientService The singleton instance of the TourCMSClientService.
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new TourCMS();
		}
		
		return self::$instance;
	}

	/**
	 * Returns the instance of the TourCMS client.
	 * 
	 * @return TourCMS The TourCMS client instance.
	 */
	public function getTourCMSClient() {
		return $this->tourCMS;
	}
}
