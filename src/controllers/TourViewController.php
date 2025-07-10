<?php

namespace Controllers;

use Services\TourViewHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;
use TourCMS\Utils\TourCMS;

class TourViewController 
{
	protected TourViewHandlerService $tourViewHandler;

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

	public function storeBookingDetails(): void
	{
		$this->tourViewHandler->submitTourBookingDetails();
	}

    public function storeDepartureDetails(): void
	{
		$this->tourViewHandler->submitDepartureDetails();
	}

    public function storeCustomerDetails(): void
	{
		$this->tourViewHandler->submitCustomerDetails();
	}
}