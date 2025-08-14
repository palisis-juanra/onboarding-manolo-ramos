<?php

namespace Helpers;

use Constants\ErrorCodes;

class ErrorHandlerHelper
{
	protected static array $errorMessages = [
		ErrorCodes::APP => [
			ErrorCodes::INCORRECT_ROUTE_ACTION => 'The defined action for the current route is not valid',
			ErrorCodes::LOGIN_REQUIRED => 'You need to be logged in order to access this page'
		],
		ErrorCodes::ROUTER => [
			ErrorCodes::ROUTE_NOT_FOUND => '404 - The route being accessed does not exist or is being accesed with an incorrect HTTP method',
			ErrorCodes::MISSING_ROUTE_DATA => 'The route is missing its handler function or access method'
		],
		ErrorCodes::CHANNELS => [
			ErrorCodes::NO_CHANNEL_DATA => 'No channel data available',
			ErrorCodes::SESS_NO_CHANNEL_ID => 'No Channel ID was found in the current session',
			ErrorCodes::POST_NO_CHANNEL_ID => 'There was an error handling the selected Channel ID'
		],
		ErrorCodes::TOUR_LIST => [
			ErrorCodes::NO_CHANNEL_TOUR_DATA => 'No tour data available for the selected channel',
			ErrorCodes::POST_NO_TOUR_ID => 'There was an error handling the selected Tour ID'
		],
		ErrorCodes::TOUR_VIEW => [
			ErrorCodes::NO_TOUR_DATA => 'No tour data available for the selected tour',
			ErrorCodes::POST_NO_TOUR_ID_VIEW => 'There was an error handling the selected Tour ID',
			ErrorCodes::POST_NO_VALID_TOUR_BOOKING_DETAILS => 'There was an error handling the selected Tour booking details',
			ErrorCodes::POST_NO_VALID_TOUR_BOOKING_RATES => 'There was an error handling the selected Tour booking rates',
			ErrorCodes::SESS_NO_CHANNEL_OR_TOUR_ID => 'No Channel or Tour ID was found in the current session',
		],
		ErrorCodes::TOUR_CHECK_AVAILABILITY => [
			ErrorCodes::EMPTY_TOUR_BOOKING_DATA => 'The Tour booking data object is empty',
			ErrorCodes::SESS_NO_CHANNEL_OR_TOUR_ID_CHECK_AVAILABILITY => 'No Channel or Tour ID was found in the current session',
			ErrorCodes::NO_AVAILABLE_COMPONENTS => 'There are no available components for the selected tour',
		],
		ErrorCodes::TOUR_DEPARTURES => [
			ErrorCodes::POST_EMPTY_DEPARTURE_CUSTOMER_DATA => 'The Customer data object is empty',
			ErrorCodes::POST_ERROR_SAVING_COMPONENT_KEY => 'There has been an error processing the selected component key',
		],
		ErrorCodes::TOUR_START_BOOKING => [
			ErrorCodes::ERROR_CREATING_TEMPORAL_BOOKING => 'There was an error creating the temporary booking'
		],
		ErrorCodes::TOUR_COMMIT_BOOKING => [
			ErrorCodes::ERROR_COMMITTING_BOOKING => 'There was an error commiting the booking with the current temp booking key'
		],
		ErrorCodes::BOOKINGS => [
			ErrorCodes::NO_BOOKINGS_DATA => 'No booking data available for the selected ID',
			ErrorCodes::POST_NO_BOOKING_ID => 'There was an error handling the selected Booking ID',
			ErrorCodes::POST_NO_MATCHING_STORED_BOOKING_ID => 'Booking ID mismatch: posted BookingID does not match stored BookingID.'
		],
		ErrorCodes::CUSTOMERS => [
			ErrorCodes::NO_CUSTOMERS_DATA => 'No customer data available for the selected ID',
			ErrorCodes::POST_ERROR_UPDATING_CUSTOMER => 'There was an error updating the customer details',
			ErrorCodes::POST_NO_CUSTOMER_ID => 'There was an error handling the selected Customer ID',
			ErrorCodes::POST_NO_MATCHING_STORED_CUSTOMER_ID => 'Customer ID mismatch: posted CustomerID does not match stored CustomerID.'
		]
	];


	public static function getErrorMessages(): array
	{
		return self::$errorMessages;
	}
}