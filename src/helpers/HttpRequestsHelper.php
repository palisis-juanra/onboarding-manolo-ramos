<?php

namespace Helpers;

class HttpRequestsHelper
{
	protected static array $httpVerbs = [
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH',
		'HEAD',
		'OPTIONS'
	];

	// TODO: improve access method to the HTTP verbs list
	/**
	 * Returns a valid HTTP verb from the list
	 *
	 * @param string $verb The verb to be matched against the known verbs list
	 * @return string $matchingVerb The matched verb
	 */
	public static function getVerb(string $verb): string
	{
		$matchingVerb = null;
		$uppercaseVerb = strtoupper($verb);

		foreach (self::$httpVerbs as $knownVerb) {
			if ($uppercaseVerb === $knownVerb) {
				$matchingVerb = $knownVerb;
				break;
			}
		}

		return $matchingVerb;
	}

	/**
	 * Checks if a verb is valid
	 *
	 * @param string $verb The verb to be checked
	 * @return bool returns the result of the comprobation
	 */
	public static function checkHttpMethod(string $verb): bool
	{
		return in_array($verb, self::$httpVerbs);
	}

	/**
	 * Compares the HTTP method used in the request with the method defined in the route.
	 *
	 * @param string $routeDefinedMethod The HTTP method defined for the route.
	 * @param string $httpRequestMethodUsed The HTTP method used in the request.
	 * @return bool True if they match, false otherwise.
	 */
	public static function compareRouteHttpMethodUsed(
		string $routeDefinedMethod, 
		string $httpRequestMethodUsed
	): bool
	{
		// Convert to uppercase
		$routeDefinedMethod = strtoupper($routeDefinedMethod);
		$httpRequestMethodUsed = strtoupper($httpRequestMethodUsed);

		return $routeDefinedMethod === $httpRequestMethodUsed;
	}
}