<?php

namespace Core;

use Services\SessionHandlerService;
use Controllers\ChannelListController;
use Helpers\HttpRequestsHelper;
use Helpers\RedirectionHelper;
use Routes\Routes;
use Services\TemplateRendererService;

class Router
{
	// Controller instances
	private $sessionHandlerService;
	private $channelListController;

	// Service Instances
	private $templateRenderer;
	
	// Member variables
	private $routes;
	private $routeNames;
	
	// Initialize the template rendering service and route collection.
	public function __construct(
		SessionHandlerService 	$sessionHandlerService,
		ChannelListController 	$channelListController,
		TemplateRendererService $templateRenderer
	)
	{
		// Get the route names for easy reference
		$this->routes = Routes::getRoutes();
		$this->routeNames = $this->getRouteNames($this->routes);
		
		// Controller instances
		$this->sessionHandlerService = $sessionHandlerService;
		$this->channelListController = $channelListController;
		// Service instances
		$this->templateRenderer = $templateRenderer;

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
					if (HttpRequestsHelper::compareRouteHttpMethodUsed( $method, $httpRequestMethodUsed)) {
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
		$requestPath = '/' . implode('/', $pathSegments);

		return $requestPath;
	}

	/**
	 * Renders the 404 error template and also throws the error code.
	 *
	 * @return void 
	 */
	private function handleNotFound(): void
	{
		$this->templateRenderer->renderTemplate('_common/error/404', []);
		http_response_code(404);
		return;
	}

	/**
	 * Handles requests to the Session Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 */
	private function handleSessionHandlerService(
		string $route, 
		string $routeMethod, 
		string $httpRequestMethodUsed
	): void
	{
		// Routes that don't require session verification
		$routesWithoutSessionCheck = [
			$this->routeNames['/'],
			$this->routeNames['/login'],
			$this->routeNames['/login/loginAction'],
		];

		// Check if the route is in the list of excluded routes or if the session is active
		if (in_array($route, $routesWithoutSessionCheck) || $this->sessionHandlerService->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/']:
					// Check if the user is logged in
					if ($this->sessionHandlerService->checkIfSessionIsActive()) {
						// Redirect to the dashboard
						RedirectionHelper::headerRedirection('/dashboard/');
					} else {
						// Redirect to the login page
						RedirectionHelper::headerRedirection('/login/');
					}

					break;

				case $this->routeNames['/login']:
					if ($this->sessionHandlerService->checkIfSessionIsActive()) {
						// Redirect to the dashboard
						RedirectionHelper::headerRedirection('/dashboard/');
					} else if ($routeMethod === HttpRequestsHelper::getVerb('GET')) {
						$this->sessionHandlerService->renderLoginPage();
					} else {
						$this->handleNotFound();
					}

					break;

				case $this->routeNames['/login/loginAction']:
					if ($routeMethod === HttpRequestsHelper::getVerb('POST')) {
						$this->sessionHandlerService->handleLogIn();
					} else {
						$this->handleNotFound();
					}

					break;

				case $this->routeNames['/logout']:
					if ($this->sessionHandlerService->checkIfSessionIsActive()) {
						$this->sessionHandlerService->handleLogOut();
					} else {
						// If the session is not active, redirect to the login page
						RedirectionHelper::headerRedirection('/login/');
					}
					
					break;

				default:
					$this->handleNotFound();
					break;
			}
		} else {
			// If the session is not active, redirect to the login page
			RedirectionHelper::headerRedirection('/login/');
		}
	}

	private function handleChannelListController(
		string $route, 
		string $routeMethod, 
		string $httpRequestMethodUsed
	): void
	{	
		// Check if the route is in the list of excluded routes or if the session is active
		if ($this->sessionHandlerService->checkIfSessionIsActive()) {
			switch ($route) {
				case $this->routeNames['/dashboard']:
					if ($this->sessionHandlerService->checkIfSessionIsActive()) {
						if ($routeMethod === HttpRequestsHelper::getVerb('GET')) {
							$this->channelListController->index();
						} else {
							$this->handleNotFound();
						}
					} else {
						// Redirect to the login page
						RedirectionHelper::headerRedirection('/login/');
					}

					break;
				case $this->routeNames['/dashboard/pickChannel']:
					if ($this->sessionHandlerService->checkIfSessionIsActive()) {
						if ($routeMethod === HttpRequestsHelper::getVerb('POST')) {
							$this->channelListController->store();
						} else {
							$this->handleNotFound();
						}
					} else {
						// Redirect to the login page
						RedirectionHelper::headerRedirection('/login/');
					}

					break;

				default:
					$this->handleNotFound();
					break;
			}
		} else {
			// If the session is not active, redirect to the login page
			RedirectionHelper::headerRedirection('/login/');
		}
	}

	/**
	 * Returns a list of route names.
	 *
	 * This method iterates through the defined routes and returns an associative array
	 * where the keys and values are the route names.
	 *
	 * @return array An associative array of route names.
	 */
	private function getRouteNames(array $routes): array
	{
		$routeNameList = [];
		foreach ($routes as $route => $routeMethod) {
			$routeNameList[$route] = $route;
		}

		return $routeNameList;
	}
}