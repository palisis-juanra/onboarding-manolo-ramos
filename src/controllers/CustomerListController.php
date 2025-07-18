<?php

namespace Controllers;

use Services\CustomerListHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;

class CustomerListController
{
	private CustomerListHandlerService $customerListHandler;

	public function __construct(
		\TourCMS\Utils\TourCMS  $tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->customerListHandler = new CustomerListHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer,
			$errorHandler
		);
	}

	public function index(): void
	{
		$this->customerListHandler->renderCustomerListPage();
	}

	public function searchCustomerByID(): void
	{
		$this->customerListHandler->submitCustomerID();
	}
}