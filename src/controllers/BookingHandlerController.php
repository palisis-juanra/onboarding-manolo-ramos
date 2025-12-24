<?php

namespace Controllers;

use Services\BookingHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;
use TourCMS\Utils\TourCMS;

class BookingHandlerController 
{
	protected BookingHandlerService $bookingHandler;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->bookingHandler = new BookingHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function checkTourAvailability(): void
	{
		$this->bookingHandler->checkTourAvailability();
	}

	public function startNewBooking(): void
	{
		$this->bookingHandler->startNewBooking();
	}

	public function confirmBooking(): void
	{
		$this->bookingHandler->confirmBooking();
	}

	public function cancelBooking(): void
	{
		$this->bookingHandler->cancelBooking();
	}
}