<?php

namespace Helpers;

class HttpRequestsHelper
{
	public const string GET = 'GET';
	public const string POST = 'POST';
	public const string PUT = 'PUT';
	public const string DELETE = 'DELETE';
	public const string PATCH = 'PATCH';
	public const string HEAD = 'HEAD';
	public const string OPTIONS = 'OPTIONS';

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
		$routeDefinedMethod = strtoupper($routeDefinedMethod);
		$httpRequestMethodUsed = strtoupper($httpRequestMethodUsed);

		return $routeDefinedMethod === $httpRequestMethodUsed;
	}
}