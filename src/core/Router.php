<?php

namespace Core;

use Controllers\SessionController;
use Controllers\ChannelListController;

use Routes\Routes;
class Router
{
	// Controller instances
	private $sessionController;
	private $channelListController;

	// Service Instances
	private $templateRenderer;
	private $tourCMSclient;

	// Config
	private $redisConfig;
	
	// Member variables
	private $routes;
	private $routeNames;
	
	// Initialize the template rendering service and route collection.
	public function __construct($tourCMSclient, $templateRenderer, $redisConfig)
	{
		// Instances using dependency injection
		$this->tourCMSclient = $tourCMSclient;
		$this->templateRenderer = $templateRenderer;

		$this->redisConfig = $redisConfig;

		// Get the route names for easy reference
		$this->routes = Routes::getRoutes();
		$this->routeNames = $this->getRouteNames($this->routes);
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
	public function dispatch()
	{
		// Extract the last part of the current URL
		$currentURL = $this->parseURL($_SERVER['REQUEST_URI']);
		// Get the HTTP method of the request (GET, POST, etc.)
		$httpRequestMethodUsed = $_SERVER['REQUEST_METHOD'];

		foreach ($this->routes as $route => $routeMethod) {
			// Check if the current URL matches the saved route pattern
			if (preg_match('#^' . $route . '$#', $currentURL, $action)) {
				// If the HTTP method used is not defined for this route, skip to the next route
				if (isset($httpRequestMethodUsed) && !isset($routeMethod[$httpRequestMethodUsed])) {
					continue;
				}
				
				$controllerFunction = null;
				$routeDefinedMethod = null;

				// Loop through the methods defined for the current route and find the matching one. Then, initialize the controller function name and the HTTP method used for the route
				foreach ($routeMethod as $method => $routeDetails) {
					// Make sure that the method that is defined in the route, matches the HTTP method used in the request
					if ($this->compareRouteHttpMethodUsed($method, $httpRequestMethodUsed)) {
						$controllerFunction = $routeDetails['controller'];
						$routeDefinedMethod = $routeDetails['method'];
						break;
					}
				}

				// If no controller function or method is defined for the route, handle not found
				if ($controllerFunction === null || $routeDefinedMethod === null) {
					$this->handleNotFound();
					return;
				}

				// Call the controller function with the route, saved method, and the current HTTP request method used
				$this->$controllerFunction(
					$route, 
					$routeDefinedMethod,
					$httpRequestMethodUsed
				);

				return;
			}
		}

		// Handle any unknown endpoints by redirecting to the 404 page
		$this->handleNotFound();
	}

	/**
	 * Parses the URL to determine the complete request path.
	 *
	 * @param string $url The URL to parse.
	 * @return string The complete endpoint path.
	 */
	private function parseURL($currentURL)
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
		$requestPath = '/' . implode('/', $pathSegments);

		return $requestPath;
	}

	/**
	 * Renders the 404 error template and also throws the error code.
	 *
	 * @return void 
	 */
	private function handleNotFound() {
		$this->templateRenderer->renderTemplate('_common/error/404', []);
		http_response_code(404);
		return;
	}

	/**
	 * Handles requests to the Session Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 */
	private function handleSessionController($route, $routeMethod, $httpRequestMethodUsed)
	{
		// Routes that don't require session verification
		$routesWithoutSessionCheck = [
			$this->routeNames['/'],
			$this->routeNames['/login'],
			$this->routeNames['/login/loginAction'],
		];

		// Initialize the Redis session handler
		$this->sessionController = new SessionController($this->redisConfig);

		// Check if the route is in the list of excluded routes or if the session is active
		if (in_array($route, $routesWithoutSessionCheck) || $this->sessionController->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/']:
					// Check if the user is logged in
					if ($this->sessionController->checkIfSessionIsActive()) {
						// Redirect to the dashboard
						header('Location: dashboard/');
						exit;
					} else {
						// Redirect to the login page
						header('Location: login/');
						exit;
					}

					break;

				case $this->routeNames['/login']:
					if ($this->sessionController->checkIfSessionIsActive()) {
						// Redirect to the dashboard
						header('Location: dashboard/');
						exit;
					} else if ($routeMethod === 'GET') {
						$this->sessionController->renderLoginPage();
					} else {
						$this->handleNotFound();
					}

					break;

				case $this->routeNames['/login/loginAction']:
					if ($routeMethod === 'POST') {
						$this->sessionController->handleLogIn();
					} else {
						$this->handleNotFound();
					}

					break;

				case $this->routeNames['/logout']:
					if ($this->sessionController->checkIfSessionIsActive()) {
						$this->sessionController->handleLogOut();
					} else {
						// If the session is not active, redirect to the login page
						header('Location: login/');
						exit;
					}
					
					break;

				default:
					$this->handleNotFound();
					break;
			}
		} else {
			// If the session is not active, redirect to the login page
			// TODO: fix incorrect redirection with headers
			header('Location: login/');
			exit;
		}
	}

	private function handleChannelListController($route, $routeMethod, $httpRequestMethodUsed)
	{	
		// Initialize the Channel List Controller
		$this->channelListController = new ChannelListController();

		// Check if the route is in the list of excluded routes or if the session is active
		if ($this->sessionController->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/dashboard']:
					if ($this->sessionController->checkIfSessionIsActive()) {
						if ($routeMethod === 'GET') {
							$this->channelListController->renderChannelListPage();
						} else {
							$this->handleNotFound();
						}
					} else {
						// Redirect to the login page
						header('Location: login/');
						exit;
					}

					break;

				default:
					$this->handleNotFound();
					break;
			}
		} else {
			// If the session is not active, redirect to the login page
			header('Location: login/');
			exit;
		}
	}

	/**
	 * Compares the HTTP method used in the request with the method defined in the route.
	 *
	 * @param string $routeDefinedMethod The HTTP method defined for the route.
	 * @param string $httpRequestMethodUsed The HTTP method used in the request.
	 * @return bool True if they match, false otherwise.
	 */
	private function compareRouteHttpMethodUsed($routeDefinedMethod, $httpRequestMethodUsed): bool
	{
		// Convert to uppercase
		$routeDefinedMethod = strtoupper($routeDefinedMethod);
		$httpRequestMethodUsed = strtoupper($httpRequestMethodUsed);

		return $routeDefinedMethod === $httpRequestMethodUsed;
	}

	/**
	 * Returns a list of route names.
	 *
	 * This method iterates through the defined routes and returns an associative array
	 * where the keys and values are the route names.
	 *
	 * @return array An associative array of route names.
	 */
	private function getRouteNames($routes)
	{
		$routeNameList = [];
		foreach ($routes as $route => $routeMethod) {
			$routeNameList[$route] = $route;
		}

		return $routeNameList;
	}
}