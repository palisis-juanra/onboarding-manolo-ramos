<?php
namespace Services;
use TourCMS\Utils\TourCMS as TourCMS;

class TourCMSClientService
{
	private static $instance = null;
	private $marketplaceID = 0;
	private $channelID = 0;
	private $privateAPIKey = "";
	private $resultType = 'simplexml';
	private $timeout = 0;
	private $baseURL = "";

	private function __construct($marketplaceID, $privateAPIKey, $resultType) {
		$this->marketplaceID = $marketplaceID;
		$this->privateAPIKey = $privateAPIKey;
		$this->resultType = $resultType;
	}

	public static function getInstance($marketplaceID = 0, $privateAPIKey = "", $resultType = 'simplexml') {
		if (self::$instance === null) {
			self::$instance = new TourCMS($marketplaceID, $privateAPIKey, $resultType);
		}
		
		return self::$instance;
	}

	public function set_base_url($url) {
		$this->baseURL = $url;
	}

	
	public function get_base_url() {
		return $this->baseURL;
	}
}
