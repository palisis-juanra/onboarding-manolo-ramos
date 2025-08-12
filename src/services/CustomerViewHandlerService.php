<?php

namespace Services;

use Controllers\ErrorHandlerController;

class CustomerViewHandlerService
{
	private $tourCMSclient;
	private $redisClient;
	private $templateRenderer;
	private $errorHandler;

	public function __construct(
		\TourCMS\Utils\TourCMS 	$tourCMSclient,
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
}