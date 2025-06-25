<?php

namespace Helpers;

class HttpRequestsHelper
{
	private static $httpVerbs = [
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'PATCH',
		'HEAD',
		'OPTIONS'
	];

	/**
	 * Returns a valid HTTP verb from the list
	 *
	 * @param string $verb The verb to be matched against the known verbs list
	 * @return string $matchingVerb The matched verb
	 */
	public static function getVerbs(string $verb): string
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
	public static function isValid(string $verb): bool
	{
		return in_array($verb, self::$httpVerbs);
	}
}