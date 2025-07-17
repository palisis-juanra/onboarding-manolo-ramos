<?php

namespace Services;

use Constants\Paths;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use SimpleXMLElement;
use TourCMS\Utils\TourCMS;

class TourViewHandlerService 
{
	// Instances
	private $tourCMSclient; 
	private $redisClient; 
	private $templateRenderer;
	private $errorHandler;

	private $tourTemplateData;
	private $bookingComponentData;
	private $tourCustomerDetails;
	private $currentChannelDetails;
	private $currentTourID;

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

		// Retrieve current channel & current tour details
		$this->currentChannelDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentChannelDetails', 
				RedisInstanceHelper::REDIS_TYPE_STRING
			)
		, true);

		$this->currentTourID = $this->redisClient->getItemFromRedis(
			'currentTourID', 
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	/**
	 * Renders the Tour View page with the tour details and booking components.
	 *
	 * Retrieves tour details and booking component data, then renders the template.
	 *
	 * @return void
	 */
	public function renderViewTourPage(): void
	{
		$this->retrieveTourDetails();
		$this->retrieveBookingComponentDetails();

		$hasBookingComponentData = !empty($this->bookingComponentData);

		// Booking steps comprobations
		$componentFetchAttempted = $this->redisClient->getItemFromRedis(
			'componentFetchAttempted',
			RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		$departurePickAttempted = $this->redisClient->getItemFromRedis(
			'departurePickAttempted',
			RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		$customerDetailsSubmitted = $this->redisClient->getItemFromRedis(
			'customerDetailsSubmitted',
			RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		$this->templateRenderer->renderTemplate(
			'tours/tourViewPage',
			[
				'tourTemplateData' => $this->tourTemplateData,
				'hasBookingComponentData' => $hasBookingComponentData,
				'bookingComponentData' => $this->bookingComponentData ?: null,
				'tourName' => $this->tourTemplateData['tourName'],
				'channelName' => $this->currentChannelDetails['channelName'],
				'channelLogo' => $this->currentChannelDetails['channelLogo'],
				'componentFetchAttempted' => $componentFetchAttempted,
				'departurePickAttempted' => $departurePickAttempted,
				'customerDetailsSubmitted' => $customerDetailsSubmitted,
			]
		);

		// Remove the current tour component data from Redis 
		$this->redisClient->deleteItemFromRedis(
			'currentTourBookingComponentDetails',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		// Reset the component fetch attempted flag
		$this->redisClient->deleteItemFromRedis(
			'componentFetchAttempted',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		// Reset the departure pick attempted flag
		$this->redisClient->deleteItemFromRedis(
			'departurePickAttempted',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		// Reset the customer details submitted flag
		$this->redisClient->deleteItemFromRedis(
			'customerDetailsSubmitted',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	/**
	 * Submits the tour booking details from the form.
	 *
	 * Validates the POST data, stores it in Redis, and redirects to check availability.
	 *
	 * @return void
	 */
	public function submitTourBookingDetails(): void
	{
		if (!empty($_POST['tourID']) || !empty($_POST['departure_date'])) {
			$currentTourBookingDetails = [
				'tourID' => $_POST['tourID'],
				'date' => $_POST['departure_date'],
			];

			// Check if there are any rates in the POST data
			$hasRates = false;
			$totalCustomers = 0;
			foreach ($_POST as $rate_id => $rate_quantity) {
				if (strpos($rate_id, 'rate') !== false) {
					$currentTourBookingDetails['rates'][$rate_id] = $rate_quantity;
					$totalCustomers += (int) $rate_quantity;
					$hasRates = true;
				}
			}

			// Count the total number of customers and add it to the booking details
			$currentTourBookingDetails['totalCustomers'] = $totalCustomers;

			// TODO: check case when all rate values are 0
			if (!$hasRates || $totalCustomers <= 0) {
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage('POST_NO_VALID_TOUR_BOOKING_RATES')

				);
				return;
			}

			$this->redisClient->storeItemInRedis(
				'currentTourBookingDetails', 
				json_encode($currentTourBookingDetails), 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			// Save a flag to indicate that the component fetch has been attempted
			$this->redisClient->storeItemInRedis(
				'componentFetchAttempted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::CHECK_TOUR_AVAILABILITY);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('POST_NO_VALID_TOUR_BOOKING_DETAILS')
			);
		}
	}

	/**
	 * Submits the departure details for the selected tour component.
	 *
	 * Validates the POST data, stores it in Redis, and redirects to the next step.
	 *
	 * @return void
	 */
	public function submitDepartureDetails(): void
	{
		if (!empty($_POST['componentKey'])) {
			$this->redisClient->storeItemInRedis(
				'currentSelectedComponentKey',
				$_POST['componentKey'],
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->redisClient->storeItemInRedis(
				'departurePickAttempted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::TOUR_VIEW);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('POST_ERROR_SAVING_COMPONENT_KEY')
			);
		}
	}

	/**
	 * Submits the customer details for the booking.
	 *
	 * Validates the POST data, stores it in Redis, and redirects to create a new booking.
	 *
	 * @return void
	 */
	public function submitCustomerDetails(): void
	{
		if (
			!empty($_POST['customerName']) ||
			!empty($_POST['customerSurname']) ||
			!empty($_POST['customerEmail'])
		) {
			$customersDetails[] = [
				'customerName' => $_POST['customerName'],
				'customerSurname' => $_POST['customerSurname'],
				'customerEmail' => $_POST['customerEmail'],
			];
			
			$totalCustomerNumber = json_decode(
				$this->redisClient->getItemFromRedis(
					'currentTourBookingDetails', 
					RedisInstanceHelper::REDIS_TYPE_STRING
				), true
			)['totalCustomers'];

			// Generate the rest of costumer details
			for ($i = 1; $i < $totalCustomerNumber; $i++) {
				$customersDetails[] = [
					'customerName' => 'customer' . $i,
					'customerSurname' => 'surname',
					'customerEmail' => 'mail@tourcms.com',
				];
			}

			$this->redisClient->storeItemInRedis(
				'currentCustomersDetails',
				json_encode($customersDetails),
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			$this->redisClient->storeItemInRedis(
				'customerDetailsSubmitted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::CREATE_BOOKING);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('POST_EMPTY_DEPARTURE_CUSTOMER_DATA')
			);
		}
	}

	/**
	 * Retrieves the tour details from the TourCMS client and prepares the template data.
	 *
	 * If the channel ID or tour ID is not set, it throws an error.
	 *
	 * @return void
	 */
	private function retrieveTourDetails(): void
	{
		$channelID = $this->currentChannelDetails['channelID'];
		$tourID = $this->currentTourID;

		if ($channelID && $tourID) {
			$retrievedTourDetails = $this->tourCMSclient->show_tour(
				$tourID,
				$channelID
			);

			$this->buildTourDetailsTemplateData($retrievedTourDetails);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('SESS_NO_CHANNEL_OR_TOUR_ID')
			);
		}
	}

	/**
	 * Retrieves the booking component details from Redis and prepares the booking component data.
	 *
	 * If no booking component details are found, it throws an error.
	 *
	 * @return void
	 */
	private function retrieveBookingComponentDetails(): void
	{
		$bookingComponentDetails = $this->redisClient->getItemFromRedis('currentTourBookingComponentDetails', RedisInstanceHelper::REDIS_TYPE_STRING);

		$this->bookingComponentData = null;

		if (!empty($bookingComponentDetails)) {
			$this->bookingComponentData = json_decode($bookingComponentDetails, true);
		} else {
			$this->errorHandler->getErrorMessage('NO_AVAILABLE_COMPONENTS');
		}
	}

	/**
	 * Retrieves the customer details from Redis.
	 *
	 * If no customer details are found, it throws an error.
	 *
	 * @return void
	 */
	private function retrieveCustomerDetails(): void
	{
		$customerDetails = $this->redisClient->getItemFromRedis('currentCustomersDetails', RedisInstanceHelper::REDIS_TYPE_STRING);

		$this->tourCustomerDetails = null;

		if (!empty($customerDetails)) {
			$this->tourCustomerDetails = json_decode($customerDetails, true);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('POST_EMPTY_DEPARTURE_CUSTOMER_DATA')
			);
		}
	}

	/**
	 * Builds the template data for the tour details.
	 *
	 * Extracts relevant information from the tour details XML and stores it in the tourTemplateData property.
	 *
	 * @param SimpleXMLElement $tourDetails The XML object containing tour details.
	 * @return void
	 */
	private function buildTourDetailsTemplateData(SimpleXMLElement $tourDetails): void
	{
		if (isset($tourDetails->tour)) {
			foreach($tourDetails->tour as $details) {
				$this->tourTemplateData = [
					'tourID' => $details->tour_id,
					'tourName' => $details->tour_name,
					'tourCode' => $details->tour_code,
					'tourNextBookableDate' => $details->next_bookable_date,
					'tourLastBookableDate' => $details->last_bookable_date,
					'tourImage' => $details->images->image->url,
					'tourStartTime' => $details->start_time,
					'tourEndTime' => $details->end_time,
					'tourSummary' => $details->summary,
					'tourShortDesc' => $details->shortdesc,
					'tourPrice' => html_entity_decode($details->from_price_display),
					'bookingDataPeople' => $details->new_booking->people_selection->rate,
					'bookingDataDates' => $details->new_booking->date_selection
				];

				$this->redisClient->storeItemInRedis(
					'currentTourDetails',
					json_encode([
						'tourID' => (string) $details->tour_id,
						'tourCode' => (string) $details->tour_code,
						'tourName' => (string) $details->tour_name,
						'tourImage' => (string) $details->images->image->url,
					]),
					RedisInstanceHelper::REDIS_TYPE_STRING
				);
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_TOUR_DATA')
			);
		}
	}
}