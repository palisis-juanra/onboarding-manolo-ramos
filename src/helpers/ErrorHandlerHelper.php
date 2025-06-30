<?php

namespace Helpers;

class ErrorHandlerHelper 
{
	protected static $errorMessages = [
		"SESS_NO_CHANNEL_ID" => 'No Channel ID was found in the current session',
		"POST_NO_CHANNEL_ID" => 'There was an error handling the selected Channel ID'


	];

	public static function getErrorMessages(string $errorCode): string
	{
		$errorMessage = array_search($errorCode, self::$errorMessages);

		return $errorMessage;
	}
}