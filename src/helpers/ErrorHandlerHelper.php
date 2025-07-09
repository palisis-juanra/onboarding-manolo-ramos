<?php

namespace Helpers;

class ErrorHandlerHelper 
{
	protected static array $errorMessages = [
		'APP' => [
			'INCORRECT_ROUTE_ACTION' => 'The defined action for the current route is not valid',
			'LOGIN_REQUIRED' => 'You need to be logged in order to access this page'
		],
		'ROUTER' => [
			'ROUTE_NOT_FOUND' => '404 - The route being accessed does not exist',
			'MISSING_ROUTE_DATA' => 'The route is missing its handler function or access method'
		],
		'CHANNELS' => [
			'NO_CHANNEL_DATA' => 'No channel data available',
			'SESS_NO_CHANNEL_ID' => 'No Channel ID was found in the current session',
			'POST_NO_CHANNEL_ID' => 'There was an error handling the selected Channel ID'
		],
		'TOUR_LIST' => [
			'NO_CHANNEL_TOUR_DATA' => 'No tour data available for the selected channel',
			'POST_NO_TOUR_ID' => 'There was an error handling the selected Tour ID'
		],
		'TOUR_VIEW' => [
			'NO_TOUR_DATA' => 'No tour data available for the selected tour',
			'POST_NO_TOUR_ID_VIEW' => 'There was an error handling the selected Tour ID',
			'POST_NO_VALID_TOUR_BOOKING_DETAILS' => 'There was an error handling the selected Tour booking details',
			'POST_NO_VALID_TOUR_BOOKING_RATES' => 'There was an error handling the selected Tour booking rates',
			'SESS_NO_CHANNEL_OR_TOUR_ID' => 'No Channel or Tour ID was found in the current session',
		],
		'TOUR_CHECK_AVAILABILITY' => [
			'EMPTY_TOUR_BOOKING_DATA' => 'The Tour booking data object is empty',
			'SESS_NO_CHANNEL_OR_TOUR_ID' => 'No Channel or Tour ID was found in the current session',
			'NO_AVAILABLE_COMPONENTS' => 'There are no available components for the selected tour',
		]
	];

	public static function getErrorMessages(): array
	{
		return self::$errorMessages;
	}
}