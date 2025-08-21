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

	/**
	 * Renders the customer list page.
	 *
	 * This method retrieves customer data and renders the customer list page.
	 *
	 * @return void
	 */
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

		// Render just the search form if no data has been submitted
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

	/**
	 * Submits the customer ID for further processing.
	 *
	 * This method retrieves the customer ID from the POST request and stores it in Redis.
	 * It then redirects to the customer list page.
	 *
	 * @return void
	 */
	public function submitCustomerID(): void
	{
		if (isset($_POST['customerID'])) {
			$customerID = $_POST['customerID'];
			
			// Store the customer ID in Redis for further processing
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

	/**
	 * Retrieves customer data based on the submitted customer ID.
	 *
	 * This method fetches customer data from the TourCMS API and builds the template data.
	 *
	 * @param bool $isCustomerIDsubmitted
	 * @return void
	 */
	public function submitCustomerEditData(): void
	{
		if (
			isset($_POST['customerEditID']) &&
			isset($_POST['customerEditName']) &&
			isset($_POST['customerEditSurname'])
		) {
			$storedCustomerID = $this->redisClient->getItemFromRedis(
				'currentCustomerID',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			// Check if the posted ID matches the stored ID
			if ($storedCustomerID !== $_POST['customerEditID']) {
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_MATCHING_STORED_CUSTOMER_ID)
				);
				
				exit;
			}

			$customerEditData = [
				"customerID" => $_POST['customerEditID'] ,
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

	/**
	 * Retrieves customer data based on the submitted customer ID.
	 *
	 * This method fetches customer data from the TourCMS API and builds the template data.
	 *
	 * @param bool $isCustomerIDsubmitted
	 * @return void
	 */
	private function retrieveCustomer(bool $isCustomerIDsubmitted): void
	{
		if ($this->currentChannelDetails['channelID'] && $isCustomerIDsubmitted) {
			$currenCustomerID = $this->redisClient->getItemFromRedis(
				'currentCustomerID',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$customerResult =$this->tourCMSclient->show_customer(
				$currenCustomerID, 
				$this->currentChannelDetails['channelID']
			);

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

	/**
	 * Builds the customer template data from the API response.
	 *
	 * This method processes the customer data retrieved from the TourCMS API
	 * and prepares it for rendering in the template.
	 *
	 * @param SimpleXMLElement $customerResult
	 * @return void
	 */
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