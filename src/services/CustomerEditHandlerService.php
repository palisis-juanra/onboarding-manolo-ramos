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
	private $customerDetails;
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
		$this->retrieveCustomerDetails();

		$customerEditAttempted = $this->redisClient->getItemFromRedis(
				'customerEditAttempted',
				RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		$this->templateRenderer->renderTemplate(
			'tours/tourViewPage',
			[
				'customerEditAttempt'   => $customerEditAttempted,
				'customerFirstName'          => $this->customerTemplateData['customerFirstName'] ?? '',
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

	private function retrieveCustomerDetails(): void
	{

	}

	private function buildEditCustomerTemplateData(): void
	{
		if (empty($this->customerDetails->customer) || $this->customerDetails->error != 'OK'){
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);
		}

		foreach($this->customerDetails as $details) {
			$this->tourTemplateData = [
				'tourID' => $details->tour_id,
			];
		}
	}

	private function buildCustomerUpdateDataObject(array $customerUpdateData): SimpleXMLElement
	{
		$customerUpdateDataObject = new SimpleXMLElement('<customer/>');

		$customerUpdateDataObject->addChild('customer_id', $customerUpdateData['customerID']);
		$customerUpdateDataObject->addChild('firstname', $customerUpdateData['customerFirstname']);
		$customerUpdateDataObject->addChild('surname', $customerUpdateData['customerID']);
		$customerUpdateDataObject->addChild('email', $customerUpdateData['customerID']);

		return $customerUpdateDataObject;
	}
}