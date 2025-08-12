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
		$isCustomerIDsubmitted = $this->redisClient->getItemFromRedis(
				'isCustomerIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		// Render just the search form if no data has not been submitted
		if (!$isCustomerIDsubmitted) {
			$this->templateRenderer->renderTemplate(
				Templates::CUSTOMER_LIST,
				[
					'isCustomerIDsubmitted' => $isCustomerIDsubmitted
				]
			);

			exit;
		}

		$this->retrieveCustomer($isCustomerIDsubmitted);

		if (!empty($this->customerTemplateData)) {
			$this->templateRenderer->renderTemplate(
				Templates::CUSTOMER_LIST,
				[
					'customerTemplateData' => $this->customerTemplateData,
					'isCustomerIDsubmitted' => $isCustomerIDsubmitted
				]
			);

		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}

		$this->redisClient->deleteItemFromRedis(
			'isCustomerIDsubmitted',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	public function submitCustomerID(): void
	{
		if (isset($_POST['customerID'])) {
			$customerID = $_POST['customerID'];
			
			$this->redisClient->storeItemInRedis(
				'currentCustomerID',
				$customerID,
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->redisClient->storeItemInRedis(
				'isCustomerIDsubmitted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::CUSTOMER_LIST);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_CUSTOMER_ID)
			);
		}
	}

	public function submitCustomerEditData(): void
	{
		if (
			isset($_POST['customerEditID']) &&
			isset($_POST['customerEditName']) &&
			isset($_POST['customerEditSurname'])
		) {

			$customerEditData = [
				"customerID" => $_POST['customerEditID'],
				"customerName" => $_POST['customerEditName'],
				"customerSurname" => $_POST['customerEditSurname'],
			];

			$this->redisClient->storeItemInRedis(
				'currentCustomerEditDetails',
				json_encode($customerEditData),
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			// TODO: remove if unnecessary
			$this->redisClient->storeItemInRedis(
				'isCustomerEditAttempted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::CUSTOMER_EDIT);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_CUSTOMER_ID)
			);
		}
	}

	private function retrieveCustomer($isCustomerIDsubmitted): void
	{
		if ($this->currentChannelDetails['channelID'] && $isCustomerIDsubmitted) {
			$currenCustomerID = $this->redisClient->getItemFromRedis(
				'currentCustomerID',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$customerResult =$this->tourCMSclient->show_customer($currenCustomerID, $this->currentChannelDetails['channelID']);

			$this->buildCustomerTemplateData($customerResult);
		} else {
			$this->redisClient->deleteItemFromRedis(
				'isCustomerIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);
		}
	}

	private function buildCustomerTemplateData(SimpleXMLElement $customerResult): void
	{
		if(empty($customerResult->customer) || $customerResult->error != 'OK') {
			$this->redisClient->deleteItemFromRedis(
				'isCustomerIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}

		$this->customerTemplateData[] = [
			'customerID' 		=> (string) $customerResult->customer->customer_id ?? '',
			'customerName' 		=> (string) $customerResult->customer->firstname ?? '',
			'customerSurname' 	=> (string) $customerResult->customer->surname ?? ''
		];
	}
}