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
		'/bookings' => [
			'GET' => [
				'handler' => 'handleBookingListController',
				'controller' => 'bookingListController',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/bookings/searchBookingByID' => [
			'POST' => [
				'handler' => 'handleBookingListController',
				'controller' => 'bookingListController',
				'method' => 'POST',
				'action' => 'searchBookingByID',
				'requiresLogIn' => true
			]
		],
		'/bookings/showBooking' => [
			'GET' => [
				'handler' => 'handleBookingListController',
				'controller' => 'bookingListController',
				'method' => 'GET',
				'action' => 'show',
				'requiresLogIn' => true
			]
		],
		'/dashboard' => [
			'GET' => [
				'handler' => 'handleChannelListController',
				'controller' => 'channelListController',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'handler' => 'handleChannelListController',
				'controller' => 'channelListController',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
			]
		],
		'/tourList' => [
			'GET' => [
				'handler' => 'handleTourListController',
				'controller' => 'tourListController',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/tourList/pickTour' => [
			'POST' => [
				'handler' => 'handleTourListController',
				'controller' => 'tourListController',
				'method' => 'POST',
				'action' => 'store',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView' => [
			'GET' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourViewController',
				'method' => 'GET',
				'action' => 'show',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/pickBookingDetails' => [
			'POST' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourViewController',
				'method' => 'POST',
				'action' => 'storeBookingDetails',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/pickDeparture' => [
			'POST' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourViewController',
				'method' => 'POST',
				'action' => 'storeDepartureDetails',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/submitCustomerDetails' => [
			'POST' => [
				'handler' => 'handleTourViewController',
				'controller' => 'tourViewController',
				'method' => 'POST',
				'action' => 'storeCustomerDetails',
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
		'/tourList/tourView/createBooking' => [
			'GET' => [
				'handler' => 'handleBookingHandlerController',
				'controller' => 'bookingController',
				'method' => 'GET',
				'action' => 'startNewBooking',
				'requiresLogIn' => true
			]
		],
		'/tourList/tourView/bookingConfirmation' => [
			'GET' => [
				'handler' => 'handleBookingHandlerController',
				'controller' => 'bookingController',
				'method' => 'GET',
				'action' => 'confirmBooking',
				'requiresLogIn' => true
			]
		],
		'/customers' => [
			'GET' => [
				'handler' => 'customerListControllerHandler',
				'controller' => 'customerListController',
				'method' => 'GET',
				'action' => 'index',
				'requiresLogIn' => true
			]
		],
		'/customers/searchCustomerByID' => [
			'POST' => [
				'handler' => 'customerListControllerHandler',
				'controller' => 'customerListController',
				'method' => 'POST',
				'action' => 'searchCustomerByID',
				'requiresLogIn' => true
			]
		],
		'/customer/showCustomer' => [
			'GET' => [
				'handler' => 'customerViewControllerHandler',
				'controller' => 'customerViewController',
				'method' => 'GET',
				'action' => 'show',
				'requiresLogIn' => true
			]
		],
		'/customers/editCustomer' => [
			'GET' => [
				'handler' => 'customerEditControllerHandler',
				'controller' => 'customerEditController',
				'method' => 'GET',
				'action' => 'edit',
				'requiresLogIn' => true
			]
		],
		'/customers/editCustomer/saveEdits' => [
			'POST' => [
				'handler' => 'customerEditControllerHandler',
				'controller' => 'customerEditController',
				'method' => 'POST',
				'action' => 'update',
				'requiresLogIn' => true
			]
		]
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
