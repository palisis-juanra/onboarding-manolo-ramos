<?php

namespace Services;

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
		, true);
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
				$this->errorHandler->getErrorMessage('EMPTY_TOUR_BOOKING_DATA')
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

				foreach ($availabilityQueryResult->available_components->component as $component)
				{
					$retrievedBookingComponents = [
						'componentKey' => (string) $component->component_key,
						'dateCode' => (string) $component->date_code,
						'startDate' => (string) $component->start_date,
						'endDate' => (string) $component->end_date,
						'dateType' => (string) ucfirst($component->date_type),
						'note' => (string) ucfirst($component->note),
						'totalPrice' => (string) html_entity_decode($component->total_price_display)
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

			RedirectionHelper::doRedirection('/tourList/tourView/');
		}
	}

    public function startNewBooking(): void
    {
        $papas = $this->redisClient->getItemFromRedis(
            'currentSelectedComponentKey',
            RedisInstanceHelper::REDIS_TYPE_STRING
        );

        $pepes = $this->redisClient->getItemFromRedis(
            'currentCustomerDetails',
            RedisInstanceHelper::REDIS_TYPE_STRING
        );

        $bookingDataObject = $this->buildBookingDataObject();
        $bookingResult = $this->tourCMSclient->start_new_booking($bookingData, $this->currentChannelDetails['channelID']);

        $temporaryBoookingData = $bookingResult->booking;
    }

    public function commitBooking(): void
    {

    }

    private function buildBookingDataObject(array $bookingData): SimpleXMLElement
    {

        $bookingDataObject = new SimpleXMLElement('<booking />');

        // Add total customers
        $bookingDataObject->addChild('total_customers', '1');

        // Append a container for the components to be booked
        $components = $bookingDataObject->addChild('components');

        // TODO: Loop through component data
        // Add a component node for each item to add to the booking
        $component = $components->addChild('component');

        // "Component key" obtained via call to "Check availability"
        $component->addChild('component_key', 'COMPONENT_KEY_HERE');


        // TODO: customers
        $customers = $bookingDataObject->addChild('customers');

        // Optionally append the customer details
        // Either add their details (as here)
        // OR an existing customer_id
        // OR leave blank and TourCMS will create a blank customer
        $customer = $customers->addChild('customer');
        $customer->addChild('title', 'Mr');
        $customer->addChild('firstname', 'Joe');
        $customer->addChild('surname', 'Bloggs');
        $customer->addChild('email', 'Email');
    }

	// Build the query string for the TourCMS API
	// TODO: find a generic approach to build query strings
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