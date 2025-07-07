<?php

namespace Services;

use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use TourCMS\Utils\TourCMS;

class BookingHandlerService 
{
	// Instances
	private $tourCMSclient; 
	private $redisClient; 
	private $templateRenderer;
	private $errorHandler;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
		$this->errorHandler = $errorHandler;
	}

	public function checkTourAvailability(): void
	{
		// Logic to check tour availability goes here
	}
}