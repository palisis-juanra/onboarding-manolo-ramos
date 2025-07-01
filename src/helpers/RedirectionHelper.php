<?php

namespace Helpers;

class RedirectionHelper
{
	/**
	 * Fires a header redirection to the specified location
	 *
	 * @param string $location The location to which the application will be redirected. Specify with left slashes /location/
	 */
	public static function doRedirection(string $location): void
	{
		$redirectUrl = dirname($_SERVER['SCRIPT_NAME']) . $location;
		header('Location: '. $redirectUrl .'');
		exit;
		
	}
}