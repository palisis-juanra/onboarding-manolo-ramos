<?php

namespace Core;

use Services\TemplateRendererService;
use Controllers\SessionController;
use Routes\Routes;
class Router
{
	private $templateRenderer;
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
		$requestMethodUsed = $_SERVER['REQUEST_METHOD'];

		foreach ($this->routes as $route => $routeDetails) {
			if (preg_match('#^' . $route . '$#', $currentURL, $action)) {
				$controllerFunction = $routeDetails['details']['controller'];
				$routeMethod = $routeDetails['details']['method'];
				$this->$controllerFunction(
					$route, 
					$routeMethod,
					$requestMethodUsed
				);

				return;
			}
		}

		// Handle any unknown endpoints by redirecting to the 404 page
		self::handleNotFound();
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
	 * Renders the 404 error template.
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
	private function handleSessionController($route, $method)
	{
		$sessionController = new SessionController($this->redisConfig);

		switch ($route) {
			case '/':
				// TODO: Implement redirection to login if not logged in, redirection to dashboard if logged in, 
				echo 'Implement session and redirection login here.';
				break;
			case '/login':
				if ($method === 'POST') {
					$sessionController->logIn();
				} else if ($method === 'GET') {
					self::handleNotFound();
				}
				break;
			case '/logout':
				$sessionController->logOut();
				break;
			default:
				self::handleNotFound();
				break;
		}
	}

	/**
	 * 
	 * Redis integrations
	 * 
	 */
	private function isLoggedIn(): bool
	{
		// The session controller must return a Redis flag to indicate if there's a session or not 
		return true;
	}
}