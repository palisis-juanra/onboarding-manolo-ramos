<?php

namespace Helpers;

class ErrorHandlerHelper 
{
	protected static $errorMessages = [
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