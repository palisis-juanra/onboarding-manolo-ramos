<?php

namespace Helpers;

class ErrorHandlerHelper 
{
	protected static $errorMessages = [
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
		'TOURS' => [
			'NO_TOUR_DATA' => 'No tour data available for the selected channel'
		]
	];

	public static function getErrorMessages(): array
	{
		return self::$errorMessages;
	}
}