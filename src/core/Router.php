<?php

namespace Core;

use Constants\ErrorCodes;
use Constants\Paths;
use Controllers\ErrorHandlerController;
use Controllers\SessionHandlerController;
use Helpers\HttpRequestsHelper;
use Helpers\RedirectionHelper;
use Routes\Routes;

class Router
{
	// Controller instances
	private SessionHandlerController $sessionHandler;
	private ErrorHandlerController $errorHandler;

	private array $routes;
	private array $routeNames;
	private array $routeDetails;

	public function __construct(
		SessionHandlerController 	$sessionHandlerController,
		ErrorHandlerController		$errorHandlerController,
	)
	{
		// Get route names for easy reference
		$this->routes = Routes::getRoutes();
		$this->routeNames = Routes::getRouteNames();

		$this->sessionHandler = $sessionHandlerController;
		$this->errorHandler = $errorHandlerController;
	}

	/**
	 * Dispatches the request to the appropriate controller based on the current URL.
	 *
	 * This method checks the current URL against the defined routes and calls the
	 * corresponding controller method if a match is found. If no match is found,
	 * it renders a 404 error page.
	 *
	 * @return void
	 */
	public function dispatch(): void
	{
		// Extract the last part of the current URL
		$currentURL = $this->parseURL($_SERVER['REQUEST_URI']);
		// Get the HTTP method used in the current request
		$httpRequestMethodUsed = $_SERVER['REQUEST_METHOD'];

		// TODO: future router refactor to include direct index search instead of looping through all routes.
		// if (array_key_exists($currentURL, $this->routes)) {
		// 	$routeData = $this->routes[$currentURL];
		// }
		
		foreach ($this->routes as $route => $routeData) {
			// Check if the current URL matches the saved route pattern
			if (preg_match('#^' . $route . '$#', $currentURL, $action)) {
				// If the HTTP method used is not defined for this route, skip to the next route
				if (isset($httpRequestMethodUsed) && !isset($routeData[$httpRequestMethodUsed])) {
					continue;
				}
				
				$handlerFunction = null;
				$routeDefinedMethod = null;
				$currentRouteDetails = null;

				// Loop through the methods defined for the current route and find the matching one. Then, initialize the controller function name and the HTTP method used for the route
				foreach ($routeData as $method => $details) {
					// Make sure that the method that is defined in the route, matches the HTTP method used in the request
					if (HttpRequestsHelper::compareRouteHttpMethodUsed( $method, $httpRequestMethodUsed)) {
						$handlerFunction = $details['handler'];
						$routeDefinedMethod = $details['method'];
						$currentRouteDetails = $details;
						break;
					}
				}

				if ($handlerFunction === null || $routeDefinedMethod === null) {
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::MISSING_ROUTE_DATA));
					return;
				}

				// Call the handler function with the route, saved method, and the current HTTP request method used
				$this->$handlerFunction(
					$route, 
					$currentRouteDetails,
					$routeDefinedMethod
				);

				return;
			}
		}

		RedirectionHelper::doRedirection(Paths::ERROR);
	}

	/**
	 * Returns the details for the current route.
	 * Can be returned empty if the route doesn't need to return its details.
	 *
	 * @return array routeInfo
	 */
	public function getRouteDetails(): array
	{
		return $this->routeDetails ?? [];
	}

	/**
	 * Stores route details for the current accessed endpoint.
	 *
	 * @param array $routeData
	 * @return void
	 */
	private function setRouteDetails(array $routeData): void
	{
		$this->routeDetails = $routeData;
	}

	/**
	 * Handles requests to the Session Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleSessionHandlerController(
		string 	$route, 
		array 	$routeData,
		string 	$routeMethod
	): void
	{
		// Routes that don't require session verification
		$routesWithoutSessionCheck = [
			$this->routeNames['/'],
			$this->routeNames['/login/loginAction']
		];

		if (in_array($route, $routesWithoutSessionCheck) || $this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						RedirectionHelper::doRedirection(Paths::DASHBOARD);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/login/loginAction']:
					if ($routeMethod === HttpRequestsHelper::POST) {
						// TODO: handle empty for sessionHandler
						$this->setRouteDetails($routeData);
					} else {
						$this->errorHandler->index(
							$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
						);
					}

					break;

				case $this->routeNames['/logout']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$this->setRouteDetails($routeData);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}
					
					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			RedirectionHelper::doRedirection(Paths::LOGIN);
		}
	}

	/**
	 * Handles requests to the Channel List Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleChannelListController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{	
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/dashboard']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData) 
						: 
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/dashboard/pickChannel']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::POST ?
							$this->setRouteDetails($routeData)
						:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Tour List Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleTourListController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{	
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/tourList']:
				case $this->routeNames['/tourList/tourView']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData) 
						: 
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/tourList/pickTour']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::POST ?
							$this->setRouteDetails($routeData)
						:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Tour View Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleTourViewController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/tourList/tourView']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData) 
						: 
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/tourList/tourView/pickDeparture']:
				case $this->routeNames['/tourList/tourView/pickBookingDetails']:
				case $this->routeNames['/tourList/tourView/submitCustomerDetails']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::POST ?
							$this->setRouteDetails($routeData) 
						: 
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Customer View Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function customerListControllerHandler(
		string 	$route,
		array 	$routeData,
		string 	$routeMethod
	): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/customers']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData)
						:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;
				
					case $this->routeNames['/customers/searchCustomerByID']:
					case $this->routeNames['/customers/submitCustomerEditData']:
						if ($this->sessionHandler->checkIfSessionIsActive()) {
							$routeMethod === HttpRequestsHelper::POST ?
								$this->setRouteDetails($routeData)
							:
								$this->errorHandler->index(
									$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
								);
						} else {
							RedirectionHelper::doRedirection(Paths::LOGIN);
						}

					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Customer Edit Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function customerEditControllerHandler(
		string 	$route,
		array 	$routeData,
		string 	$routeMethod
	): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/customers/editCustomer']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData)
							:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/customers/editCustomer/update']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::POST ?
							$this->setRouteDetails($routeData)
							:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

				break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Booking List Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleBookingListController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{			
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/bookings']:
				case $this->routeNames['/bookings/showBooking']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData)
						:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				case $this->routeNames['/bookings/searchBookingByID']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::POST ?
							$this->setRouteDetails($routeData)
						:
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

				break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}	
	}

	/**
	 * Handles requests to the Booking Handler Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleBookingHandlerController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/tourList/tourView/createBooking']:
				case $this->routeNames['/tourList/tourView/bookingConfirmation']:
				case $this->routeNames['/tourList/tourView/checkTourAvailability']:
					if ($this->sessionHandler->checkIfSessionIsActive()) {
						$routeMethod === HttpRequestsHelper::GET ?
							$this->setRouteDetails($routeData) 
						: 
							$this->errorHandler->index(
								$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
							);
					} else {
						RedirectionHelper::doRedirection(Paths::LOGIN);
					}

					break;

				default:
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);

					break;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::LOGIN_REQUIRED)
			);
		}
	}

	/**
	 * Handles requests to the Login Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleLoginHandlerController(
		string 	$route,
		array 	$routeData, 
		string 	$routeMethod
	): void
	{	
		switch ($route) {
			case $this->routeNames['/login']:
				if ($this->sessionHandler->checkIfSessionIsActive()) {
					RedirectionHelper::doRedirection(Paths::DASHBOARD);
				} else if ($routeMethod === HttpRequestsHelper::GET) {
					$this->setRouteDetails($routeData);
				} else {
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
				}

				break;

			case $this->routeNames['/login/loginAction']:
				if ($routeMethod === HttpRequestsHelper::POST) {
					$this->setRouteDetails($routeData);
				} else {
					$this->errorHandler->index(
						$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
					);
				}

				break;

			default:
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
				);

				break;
		}
	}

	/**
	 * Handles requests to the Error Handler Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 * @param array $routeData The parameters data associated to the route.
	 * @param string $routeMethod The HTTP method configured for the route.
	 */
	private function handleErrorHandlerController(
		string 	$route,
		array 	$routeData,
		string 	$routeMethod
	): void
	{
		if ($this->routeNames['/error'] == $route) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::ROUTE_NOT_FOUND)
			);
		}
	}

	/**
	 * Parses the URL to determine the complete request path.
	 *
	 * @param string $currentURL The URL to parse.
	 * @return string The complete endpoint path.
	 */
	private function parseURL(string $currentURL): string
	{
		// Full URL
		$urlParts = parse_url($currentURL);
		// Divide the path into segments
		$pathSegments = explode('/', $urlParts['path']);
		// Filter for empty segments
		$pathSegments = array_filter($pathSegments);
		// Remove the first segment (localhost or domain)
		$pathSegments = array_slice($pathSegments, 1);

		// Build the path before returning it
		return '/' . implode('/', $pathSegments);
	}
}