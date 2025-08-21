<?php

namespace Services;

use Constants\ErrorCodes;
use Constants\Paths;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use SimpleXMLElement;

use TourCMS\Utils\TourCMS;

class BookingHandlerService 
{
	// Instances
	private $tourCMSclient; 
	private $redisClient; 
	private $templateRenderer;
	private $errorHandler;

	private $currentChannelDetails;

	public function __construct(
		TourCMS 				$tourCMSclient,
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
		,true);
	}

	/**
	 * Checks the availability of the current tour.
	 *
	 * Retrieves booking details from Redis, builds the query parameters,
	 * queries the TourCMS API for availability, and stores available components in Redis.
	 * Redirects the user based on the query result.
	 *
	 * @return void
	 */
	public function checkTourAvailability(): void
	{
		$currentTourBookingDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentTourBookingDetails', 
				RedisInstanceHelper::REDIS_TYPE_STRING
			)
		, true );

		if (empty($currentTourBookingDetails)) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::EMPTY_TOUR_BOOKING_DATA)
		);

		} else {
			$availabilityParameters = $this->buildAvailabilityParameters($currentTourBookingDetails);

			$availabilityQueryResult = $this->tourCMSclient->check_tour_availability(
				$availabilityParameters['queryString'], 
				$availabilityParameters['tourID'],
				$this->currentChannelDetails['channelID']
			);

			// Check if there are any components available
			if (
				isset($availabilityQueryResult->available_components->component) && 
				count($availabilityQueryResult->available_components->component) > 0
			) {
				$retrievedBookingComponents = [];
				foreach ($availabilityQueryResult->available_components->component as $component) {
					$retrievedBookingComponents = [
						'componentKey' => (string) $component->component_key,
						'dateCode' => (string) $component->date_code,
						'startDate' => (string) $component->start_date,
						'endDate' => (string) $component->end_date,
						'dateType' => ucfirst($component->date_type),
						'note' => ucfirst($component->note),
						'totalPrice' => html_entity_decode($component->total_price_display)
					];
				}

			} else {
				$retrievedBookingComponents = [];
			}

			$this->redisClient->storeItemInRedis(
				'currentTourBookingComponentDetails', 
				json_encode($retrievedBookingComponents), 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::TOUR_VIEW);
		}
	}

	/**
	 * Starts a new booking by retrieving the selected component key and customer details from Redis,
	 * building the booking data object, and calling the TourCMS API to start the booking.
	 *
	 * @return void
	 */
	public function startNewBooking(): void
	{
		$selectedComponentKey = $this->redisClient->getItemFromRedis(
			'currentSelectedComponentKey',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		$tourCustomerDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentCustomersDetails',
				RedisInstanceHelper::REDIS_TYPE_STRING
			),true
		);

		$bookingDataObject = $this->buildBookingDataObject($tourCustomerDetails, $selectedComponentKey);
		$bookingResult = $this->tourCMSclient->start_new_booking($bookingDataObject, $this->currentChannelDetails['channelID']);

		if ($bookingResult->error == "OK") {
			$tempBookingKey = (string) $bookingResult->booking->booking_id;
			// Store the temporary booking key in Redis
			$this->redisClient->storeItemInRedis(
				'currentTemporaryBookingKey', 
				$tempBookingKey, 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->commitBooking($tempBookingKey);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::ERROR_CREATING_TEMPORAL_BOOKING)
			);
		}
	}

	/**
	 * Commits the booking using the temporary booking key.
	 *
	 * @param string $tempBookingKey The temporary booking key to commit.
	 * @return void
	 */
	private function commitBooking(string $tempBookingKey): void
	{
		$booking = new SimpleXMLElement('<booking />');
		$booking->addChild('booking_id', $tempBookingKey);

		$result = $this->tourCMSclient->commit_new_booking($booking, $this->currentChannelDetails['channelID']);

		if($result->error == "OK") {
			$this->redisClient->deleteItemFromRedis(
				'currentTemporaryBookingKey', 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->redisClient->storeItemInRedis(
				'bookingConfirmationDetails', 
				json_encode([
					'bookingID' => (string) $result->booking->booking_id,
					'tourDetails' => $this->redisClient->getItemFromRedis(
						'currentTourDetails',
						RedisInstanceHelper::REDIS_TYPE_STRING
					),
					'bookingDetails' => $this->redisClient->getItemFromRedis(
						'currentTourBookingDetails',
						RedisInstanceHelper::REDIS_TYPE_STRING
					),
					'customerDetails' => $this->redisClient->getItemFromRedis(
						'currentCustomersDetails',
						RedisInstanceHelper::REDIS_TYPE_STRING
					)
				]),
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::BOOKING_CONFIRMATION);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::ERROR_COMMITTING_BOOKING)
			);
		}
	}

	/**
	 * Confirms the booking by retrieving the current tour details and customer data,
	 * and preparing the confirmation data for rendering.
	 *
	 * @return void
	 */
	public function confirmBooking(): void
	{
		$bookingConfirmationDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'bookingConfirmationDetails',
				RedisInstanceHelper::REDIS_TYPE_STRING
			)
		,true);

		$tourDetails = json_decode($bookingConfirmationDetails['tourDetails'] ?? '{}', true);
		$bookingDetails = json_decode($bookingConfirmationDetails['bookingDetails'] ?? '{}', true);
		$customerDetails = json_decode($bookingConfirmationDetails['customerDetails'] ?? '{}', true);

		$bookingConfirmationData = [
			'bookingID' => $bookingConfirmationDetails['bookingID'] ?? '',
			'tourID' => $tourDetails['tourID'] ?? '',
			'tourCode' => $tourDetails['tourCode'] ?? '',
			'tourName' => $tourDetails['tourName'] ?? '',
			'tourImage' => $tourDetails['tourImage'] ?? '',
			'customerData' => $customerDetails,
			'totalCustomers' => $bookingDetails['totalCustomers'] ?? 0,
			'departureDate' => $bookingDetails['date'] ?? '',
		];

		// TODO: check if storing the booking confirmation data is necessary
		$this->redisClient->storeItemInRedis(
			'bookingConfirmationData',
			json_encode($bookingConfirmationData),
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		$this->templateRenderer->renderTemplate(
			'bookings/bookingConfirmation',
			[
				'bookingConfirmationData' => $bookingConfirmationData
			]
		);
	}

	/**
	 * Builds the booking data object for the TourCMS API.
	 *
	 * @param array $customerData The customer data to include in the booking.
	 * @param string $componentKey The component key for the booking.
	 * @return SimpleXMLElement The booking data object as an XML element.
	 */
	private function buildBookingDataObject(array $customerData, string $componentKey): SimpleXMLElement
	{
		$bookingDataObject = new SimpleXMLElement('<booking />');

		// Add total customers
		$bookingDataObject->addChild('total_customers', count($customerData));

		// Append a container for the components to be booked
		$components = $bookingDataObject->addChild('components');

		// Add a component node for each item to add to the booking
		$component = $components->addChild('component');

		// "Component key" obtained via call to "Check availability"
		$component->addChild('component_key', $componentKey);

		// Add customer details
		$customers = $bookingDataObject->addChild('customers');

		foreach ($customerData as $customer) {
			$customerNode = $customers->addChild('customer');
			$customerNode->addChild('firstname', $customer['customerName']);
			$customerNode->addChild('surname', $customer['customerSurname']);
			$customerNode->addChild('customer_email', $customer['customerEmail']);
		}

		return $bookingDataObject;
	}

	// TODO: find a generic approach to build query strings
	/**
	 * Builds the query parameters for checking tour availability.
	 *
	 * @param array $parameters The parameters including date and rates.
	 * @return array An array containing the tour ID and the query string.
	 */
	private function buildAvailabilityParameters(array $parameters): array
	{
		// Add the date to the query params
		 $queryParams = [
			'date' => $parameters['date']
		];

		foreach ($parameters['rates'] as $rate_id => $rate_quantity) {
			if (strpos($rate_id, 'rate') !== false) {
				// Clean the rate ID to use as a key
				// Remove 'rate' prefix and any leading hyphens or underscores
				$cleanRateID = str_replace('rate', '', $rate_id);
				$cleanRateID = ltrim($cleanRateID, '-_');

				$queryParams[$cleanRateID] = $rate_quantity;
			}
		}

		$queryString = http_build_query($queryParams);
		
		return [
			"tourID" => $parameters['tourID'],
			"queryString" => $queryString
		];
	}
}