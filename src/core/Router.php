<?php

namespace Core;

use Services\TemplateRendererService;
use Controllers\SessionController;
use Routes\Routes;
class Router
{
	// Instances
	private $templateRenderer;
	
	// Controller instances
	private $sessionController;

	private $routes;
	private $redisConfig;
	private $tourcmsConfig;
	
	// Initialize the template rendering service and route collection.
	public function __construct(array $envConfig)
	{
		// Retrieve config parameters for controllers
		$this->redisConfig = $envConfig['redis'];
		$this->tourcmsConfig = $envConfig['tourcms'];

		$this->routes = Routes::getRoutes();
		$this->templateRenderer = new TemplateRendererService();
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
		$this->sessionController = new SessionController($this->redisConfig);

		if ($this->sessionController->checkActiveRedisSession()){
			// TODO: evaluate if moving the logic inside each case to individual functions is suitable. 
			switch ($route) {
				case '/':
					// TODO: Implement redirection to login page if not logged in, redirection to dashboard if logged in.
					echo 'Implement session and redirection logic here.';
					header('Location: login/');
					break;
				case '/login':
					// TODO: If the user is logged in, redirect to the dashboard
					if ($routeMethod === 'GET') {
						$this->sessionController->renderLoginPage();
					} else {
						$this->handleNotFound();
					}
					break;
				case '/login/loginAction':
					if ($routeMethod === 'POST') {
						$this->sessionController->handlelogIn();
					} else {
						$this->handleNotFound();
					}
					break;
				case '/logout':
					$this->sessionController->handlelogOut();
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

	/**
	 * Compares the HTTP method used in the request with the method defined in the route.
	 *
	 * @param string $routeDefinedMethod The HTTP method defined for the route.
	 * @param string $httpRequestMethodUsed The HTTP method used in the request.
	 * @return bool True if they match, false otherwise.
	 */
	private function compareRouteHttpMethodUsed($routeDefinedMethod, $httpRequestMethodUsed): bool
	{
		// Convert to uppecase
		$routeDefinedMethod = strtoupper($routeDefinedMethod);
		$httpRequestMethodUsed = strtoupper($httpRequestMethodUsed);

		return $routeDefinedMethod === $httpRequestMethodUsed;
	}
}