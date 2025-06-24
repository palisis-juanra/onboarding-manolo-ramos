<?php

namespace Services;

use TourCMS\Utils\TourCMS as TourCMS;

class TourCMSClientService
{
	private static $instance;
	private $tourCMS;

	/**
	 * TourCMSClientService constructor.
	 *
	 * Initializes the TourCMS client with environment variables.
	 * This constructor is private to enforce the singleton pattern.
	 */
	private function __construct($tourCMSconfig)
	{
		$this->tourCMS = new TourCMS(
			$tourCMSconfig['TCMS_MARKETPLACE_ID'],
			$tourCMSconfig['TCMS_API_KEY'],
			$tourCMSconfig['TCMS_RESULT_TYPE'],
			$tourCMSconfig['TCMS_TIMEOUT'],
		);

		$this->tourCMS->set_base_url($tourCMSconfig['TCMS_API_URL']);
	}

	/**
	 * Returns the singleton instance of the TourCMSClientService.
	 *
	 * This method ensures that only one instance of the TourCMSClientService is created
	 * and returns that instance. If the instance does not exist, it creates a new one.
	 *
	 * @return TourCMSClientService The singleton instance of the TourCMSClientService.
	 */
	public static function getInstance($tourCMSconfig)
	{
		if (self::$instance === null) {
			self::$instance = new self(
				$tourCMSconfig
			);
		}
		
		return self::$instance;
	}
}
