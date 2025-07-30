<?php

namespace Services;

use Constants\ErrorCodes;
use Controllers\ErrorHandlerController;
use Helpers\RedisInstanceHelper;
use SimpleXMLElement;

class CustomerEditHandlerService
{
	private $tourCMSclient;
	private $redisClient;
	private $templateRenderer;
	private $errorHandler;

	private $currentChannelDetails;
	private $currentCustomerDetails;
	private $customerTemplateData;
	private array $tourTemplateData;

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
			)
		, true);

		$this->currentCustomerDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentCustomerEditDetails',
				RedisInstanceHelper::REDIS_TYPE_STRING
			)
		, true);
	}

	public function renderEditCustomerPage(): void
	{
		$this->buildEditCustomerTemplateData($this->currentCustomerDetails);

		$isCustomerEditAttempted = $this->redisClient->getItemFromRedis(
				'isCustomerEditAttempted',
				RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		$this->templateRenderer->renderTemplate(
			'tours/tourViewPage',
			[
				'isCustomerEditAttempted'   => $isCustomerEditAttempted,
				'customerName'     => $this->customerTemplateData['customerName'] ?? '',
				'customerSurname'       => $this->customerTemplateData['customerSurname'] ?? '',
			]
		);

		// Remove the customer edit attempted flag
		$this->redisClient->deleteItemFromRedis(
			'currentTourBookingComponentDetails',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	public function updateCustomerDetails(): void
	{
		if (!empty($_POST['customerUpdate'])) {
			$customerUpdateDetails = [
				'customerID'        => $_POST['customerID'],
				'customerFirstName' => $_POST['customerFirstName'],
				'customerSurname'   => $_POST['customerSurname']
			];

			$customerUpdateDataObject = $this->buildCustomerUpdateDataObject($customerUpdateDetails);

			$updateResult = $this->tourCMSclient->update_customer($customerUpdateDataObject,
				$this->currentChannelDetails['channelID']);

			switch ($updateResult->error) {
				case "OK":

				case "NO DATA CHANGED":

				default:

					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_ERROR_UPDATING_CUSTOMER)
			);
		}
	}

	private function buildEditCustomerTemplateData(array $customerEditData): void
	{
		if (empty($customerEditData)){
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}

		$this->tourTemplateData = $customerEditData;
	}

	private function buildCustomerUpdateDataObject(array $customerUpdateData): SimpleXMLElement
	{
		$customerUpdateDataObject = new SimpleXMLElement('<customer/>');

		$customerUpdateDataObject->addChild('customer_id', $customerUpdateData['customerID']);
		$customerUpdateDataObject->addChild('firstname', $customerUpdateData['customerFirstname']);
		$customerUpdateDataObject->addChild('surname', $customerUpdateData['customerID']);

		return $customerUpdateDataObject;
	}
}