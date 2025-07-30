<?php

namespace Services;

use Constants\ErrorCodes;
use Constants\Paths;
use Constants\Templates;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use SimpleXMLElement;

class CustomerListHandlerService
{
	private $tourCMSclient;
	private $redisClient;
	private $templateRenderer;
	private $errorHandler;

	private $currentChannelDetails;
	private $customerTemplateData;

	public function __construct(
		\TourCMS\Utils\TourCMS  $tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
		$this->errorHandler = $errorHandler;

		$this->currentChannelDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentChannelDetails',
				RedisInstanceHelper::REDIS_TYPE_STRING
			), true);
	}

	public function renderCustomerListPage(): void
	{
		if (empty($this->currentChannelDetails['channelID'])) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);

			exit;
		}

		// Check if the customer ID query has been submitted
		$customerIDsubmitted = $this->redisClient->getItemFromRedis(
				'customerIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		// Render just the search form if no data has not been submitted
		if (!$customerIDsubmitted) {
			$this->templateRenderer->renderTemplate(
				Templates::CUSTOMER_LIST,
				[
					'customerIDsubmitted' => $customerIDsubmitted
				]
			);

			exit;
		}

		$this->retrieveCustomers();

		if (!empty($this->customerTemplateData)) {
			$this->templateRenderer->renderTemplate(
				Templates::CUSTOMER_LIST,
				[
					'customerTemplateData' => $this->customerTemplateData,
					'customerIDsubmitted' => $customerIDsubmitted
				]
			);

			$this->redisClient->deleteItemFromRedis(
				'customerIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}
	}

	public function submitCustomerID(): void
	{
		if ($_POST['customerID']) {
			$customerID = $_POST['customerID'];
			
			$this->redisClient->storeItemInRedis(
				'currentCustomerID',
				$customerID,
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->redisClient->storeItemInRedis(
				'customerIDsubmitted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::CUSTOMER_LIST);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_BOOKING_ID)
			);
		}
	}
	private function retrieveCustomers(): void
	{
		if ($this->currentChannelDetails['channelID']) {
			$currenCustomerID = $this->redisClient->getItemFromRedis(
				'currentCustomerID',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$customerResult =$this->tourCMSclient->show_customer($currenCustomerID, $this->currentChannelDetails['channelID']);

			$this->buildCustomerTemplateData($customerResult);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);
		}
	}

	private function buildCustomerTemplateData(SimpleXMLElement $customerResult): void
	{
		if(empty($customerResult->customer) || $customerResult->error != 'OK') {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}

		$this->customerTemplateData[] = [
			'customerID' 		=> (string) $customerResult->customer->customer_id ?? '',
			'customerName' 		=> (string) $customerResult->customer->firstname ?? '',
			'customerSurname' 	=> (string) $customerResult->customer->surname ?? '',
			'customerEmail' 	=> (string) $customerResult->customer->email ?? '',
			// TODO: build the data
		];
	}
}