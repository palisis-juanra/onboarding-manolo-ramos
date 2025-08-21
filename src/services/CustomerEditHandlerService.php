<?php

namespace Services;

use Constants\ErrorCodes;
use Constants\Paths;
use Constants\Templates;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
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
			Templates::CUSTOMER_EDIT,
			[
				'isCustomerEditAttempted'	=> $isCustomerEditAttempted,
				'customerID'				=> $this->customerTemplateData['customerID'] ?? '',
				'customerName'				=> $this->customerTemplateData['customerName'] ?? '',
				'customerSurname' 			=> $this->customerTemplateData['customerSurname'] ?? '',
			]
		);

		// TODO: Remove the customer edit attempted flag
		// $this->redisClient->deleteItemFromRedis(
		// 	'isCustomerEditAttempted',
		// 	RedisInstanceHelper::REDIS_TYPE_STRING
		// );
	}

	/**
	 * Handles the customer update confirmation page rendering.
	 *
	 * This method retrieves the updated customer data from Redis and renders
	 * the confirmation template. If no data is found, it redirects to an error page.
	 *
	 * @return void
	 */
	public function renderCustomerUpdateConfirmation(): void
	{
		$updatedCustomerData = json_decode(
			$this->redisClient->getItemFromRedis(
				'updatedCustomerData',
				RedisInstanceHelper::REDIS_TYPE_STRING
			),
			true
		);

		if (empty($updatedCustomerData)) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CUSTOMERS_DATA)
			);

			exit;
		}

		// TODO: check if needed
		$this->redisClient->storeItemInRedis(
			'isCustomerUpdateCompleted',
			'true',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		$this->templateRenderer->renderTemplate(
			Templates::CUSTOMER_UPDATE_CONFIRMATION,
			[
				'updatedCustomerData' => $updatedCustomerData
			]
		);

		$this->redisClient->deleteItemFromRedis(
			'updatedCustomerData',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	public function updateCustomerDetails(): void
	{
		if ( 
			!empty($_POST['customerNameUpdate']) && 
			!empty($_POST['customerSurnameUpdate'])
		) {
			$customerUpdateDetails = [
				'customerID'		=> $this->currentCustomerDetails['customerID'],
				'customerFirstName'	=> $_POST['customerNameUpdate'],
				'customerSurname'	=> $_POST['customerSurnameUpdate']
			];

			$customerUpdateDataObject = $this->buildCustomerUpdateDataObject($customerUpdateDetails);

			$updateResult = $this->tourCMSclient->update_customer(
				$customerUpdateDataObject,
				$this->currentChannelDetails['channelID']
			);

			switch ($updateResult->error) {
				case "OK":
					$this->redisClient->storeItemInRedis(
						'isCustomerEditCompleted',
						'true',
						RedisInstanceHelper::REDIS_TYPE_STRING
					);

					$this->redisClient->storeItemInRedis(
						'updatedCustomerData',
						json_encode([
							'customerID' => $customerUpdateDetails['customerID'],
							'updatedCustomerName' => $customerUpdateDetails['customerFirstName'],
							'updatedCustomerSurname' => $customerUpdateDetails['customerSurname']
						]),
						RedisInstanceHelper::REDIS_TYPE_STRING
					);

					RedirectionHelper::doRedirection(Paths::CUSTOMER_UPDATE_CONFIRMATION);
					echo "Customer details updated successfully.";
					break;
				case "NO DATA CHANGED":
					echo "No changes were made to the customer details.";
					break;
				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::POST_ERROR_UPDATING_CUSTOMER)
					);
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

		$this->customerTemplateData = [
			'customerID' 		=> (string) $customerEditData['customerID'] ?? '',
			'customerName' 		=> (string) $customerEditData['customerName'] ?? '',
			'customerSurname' 	=> (string) $customerEditData['customerSurname'] ?? ''
		];
	}

	private function buildCustomerUpdateDataObject(array $customerUpdateData): SimpleXMLElement
	{
		$customerUpdateDataObject = new SimpleXMLElement('<customer/>');

		$customerUpdateDataObject->addChild('customer_id', $customerUpdateData['customerID']);
		$customerUpdateDataObject->addChild('firstname', $customerUpdateData['customerFirstName']);
		$customerUpdateDataObject->addChild('surname', $customerUpdateData['customerSurname']);

		return $customerUpdateDataObject;
	}
}