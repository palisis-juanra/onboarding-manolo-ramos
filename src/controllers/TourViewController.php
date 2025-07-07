<?php

namespace Controllers;

use Services\TourViewHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;
use TourCMS\Utils\TourCMS;

class TourViewController 
{
	protected $tourViewHandler;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->tourViewHandler = new TourViewHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function show(): void
	{
		$this->tourViewHandler->renderTourViewPage();
	}

	public function store(): void
	{
		$this->tourViewHandler->submitTourBookingDetails();
	}
}