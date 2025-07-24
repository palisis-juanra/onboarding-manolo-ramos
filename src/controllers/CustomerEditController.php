<?php

namespace Controllers;

use Services\CustomerEditHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;

class CustomerEditController
{
	private CustomerEditHandlerService $customerEditHandler;
	public function __construct(
		\TourCMS\Utils\TourCMS  $tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->customerEditHandler = new CustomerEditHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function index(): void
	{
		$this->customerEditHandler->renderEditCustomerPage();
	}

	public function update(): void
	{
		$this->customerEditHandler->updateCustomerDetails();
	}
}