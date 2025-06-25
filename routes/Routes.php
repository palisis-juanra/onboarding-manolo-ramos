<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'GET' => [
				'handler' => 'handleSessionHandlerService',
				'controller' => 'SessionHandler',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => false
			]
		],
		'/login' => [
			'GET' => [
				'handler' => 'handleSessionHandlerService',
				'controller' => 'SessionHandler',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => false
			]
		],
		'/login/loginAction' => [
			'POST' => [
				'handler' => 'handleSessionHandlerService',
				'controller' => 'SessionHandler',
				'method' => 'POST',
				'action' => 'logIn',
				'requiresLogIn' => false
			]
		],
		'/logout' => [
			'GET' => [
				'handler' => 'handleSessionHandlerService',
				'controller' => 'SessionHandler',
				'method' => 'GET',
				'action' => 'logOut',
				'requiresLogIn' => true
			]
		],
		'/dashboard' => [
			'GET' => [
				'handler' => 'handleChannelListController',
				'controller' => 'ChannelList',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'handler' => 'handleChannelListController',
				'controller' => 'ChannelList',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
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
