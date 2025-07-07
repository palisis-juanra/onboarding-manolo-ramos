<?php

namespace Services;

use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
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

			return;

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
				// We have some components, loop through them
				foreach ($availabilityQueryResult->available_components->component as $component)
				{
					echo "<pre>";
					print_r($component);
					print $component->date_code . " ";
					print $component->total_price_display . "<br />";
					echo "</pre>";
				}
			} else {
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage('NO_AVAILABLE_COMPONENTS')
				);
			}
		}
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