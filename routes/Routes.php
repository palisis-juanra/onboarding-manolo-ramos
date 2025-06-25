<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'login',
			],
		],
		'/login' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'login',
			]
		],
		'/login/loginAction' => [
			'POST' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'/logout' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'logout',
			],
		],
		'/dashboard' => [
			'GET' => [
				'controller' => 'handleChannelListController',
				'method' => 'GET',
				'action' => 'showChannelList',
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'controller' => 'handleChannelListController',
				'method' => 'POST',
				'action' => 'pickChannel',
			]
		],
	];

	/**
	 * Returns the full list of routes.
	 *
	 * @return array An associative array of full route details.
	 */
	public static function getRoutes() {
		return self::$routes;
	}

	/**
	 * Returns a list of route names.
	 *
	 * This method iterates through the defined routes and returns an associative array
	 * where the keys and values are the route names.
	 *
	 * @return array An associative array of route names.
	 */
	public static function getRouteNames(): array
	{
		$routeNameList = [];
		foreach (self::$routes as $route => $routeMethod) {
			$routeNameList[$route] = $route;
		}

		return $routeNameList;
	}
}
