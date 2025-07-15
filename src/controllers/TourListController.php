<?php

namespace Controllers;

use Services\RedisService;
use Services\TemplateRendererService;
use Services\TourListHandlerService;
use TourCMS\Utils\TourCMS;

class TourListController 
{
	protected TourListHandlerService $tourListHandler;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->tourListHandler = new TourListHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function index(): void
	{
		$this->tourListHandler->renderTourListPage();
	}

	public function store(): void
	{
		$this->tourListHandler->submitTourPick();
	}
}