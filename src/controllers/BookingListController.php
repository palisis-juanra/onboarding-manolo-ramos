<?php

namespace Controllers;

use Services\BookingListHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;
use TourCMS\Utils\TourCMS;

class BookingListController
{
	private BookingListHandlerService $bookingListHandlerService;

	public function __construct(
		TourCMS 				$tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->bookingListHandlerService = new BookingListHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function index(): void
	{
		$this->bookingListHandlerService->renderBookingListPage();
	}

	public function searchBookingByID(): void
	{
		$this->bookingListHandlerService->submitBookingID();
	}
}