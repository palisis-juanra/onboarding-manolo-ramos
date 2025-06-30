<?php

namespace Helpers;

class ErrorHandlerHelper 
{
	protected static $errorMessages = [
		'APP' => [
			'INCORRECT_ROUTE_ACTION' => 'The defined action for the current route is not valid'
		],
		'ROUTER' => [
			'ROUTE_NOT_FOUND' => 'The route being accessed does not exist',
			'MISSING_ROUTE_DATA' => 'The route is missing its handler function or access method'
		],
		'CHANNELS' => [
			'NO_CHANNEL_DATA' => 'No channel data available',
			'SESS_NO_CHANNEL_ID' => 'No Channel ID was found in the current session',
			'POST_NO_CHANNEL_ID' => 'There was an error handling the selected Channel ID'
		],
		'TOURS' => [
			'NO_TOUR_DATA' => 'No tour data available'
		]
	];

	public static function getErrorMessages(string $errorCode): string
	{
		foreach (self::$errorMessages as $type => $messages) {
			if (array_key_exists($errorCode, $messages)) {
				return $messages[$errorCode];
			}
		}

		return 'Unknown error code: ' . $errorCode;
	}
}