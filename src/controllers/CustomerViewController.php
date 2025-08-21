<?php

namespace Controllers;

use Services\CustomerViewHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;

class CustomerViewController
{
	private CustomerViewHandlerService $customerViewHandler;

	public function __construct(
		\TourCMS\Utils\TourCMS 	$tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->customerViewHandler = new CustomerViewHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function index(): void
	{
		$this->customerViewHandler->renderCustomerViewPage();
	}
}