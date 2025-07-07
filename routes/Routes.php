<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'GET' => [
				'handler' => 'handleSessionHandlerController',
				'controller' => false,
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => false
			]
		],
		'/login' => [
			'GET' => [
				'handler' => 'handleLoginHandlerController',
				'controller' => 'loginController',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => false
			]
		],
		'/login/loginAction' => [
			'POST' => [
				'handler' => 'handleSessionHandlerController',
				'controller' => 'sessionHandler',
				'method' => 'POST',
				'action' => 'logIn',
				'requiresLogIn' => false
			]
		],
		'/logout' => [
			'GET' => [
				'handler' => 'handleSessionHandlerController',
				'controller' => 'sessionHandler',
				'method' => 'GET',
				'action' => 'logOut',
				'requiresLogIn' => true
			]
		],
		'/error' => [
			'GET' => [
				'handler' => 'handleErrorHandlerController',
				'controller' => 'errorHandler',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => false
			]
		],
		'/dashboard' => [
			'GET' => [
				'handler' => 'handleChannelListController',
				'controller' => 'channelList',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'handler' => 'handleChannelListController',
				'controller' => 'channelList',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
			]
		],
		'/tourList' => [
			'GET' => [
				'handler' => 'handleTourListController',
				'controller' => 'tourList',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/tourList/pickTour' => [
			'POST' => [
				'handler' => 'handleTourListController',
				'controller' => 'tourList',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView' => [
			'GET' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourView',
				'method' => 'GET',
				'action' => 'show',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/pickBookingDetails' => [
			'POST' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourView',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/checkTourAvailability' => [
			'GET' => [
				'handler' => 'handleBookingHandlerController',
				'controller' => 'bookingController',
				'method' => 'GET',
				'action' => 'checkTourAvailability',
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
