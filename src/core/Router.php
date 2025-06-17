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

		foreach ($this->routes as $route => $routeDetails) {
			if (preg_match('#^' . $route . '$#', $currentURL, $action)) {
				$controllerMethod = $routeDetails['details']['controller'];
				$this->$controllerMethod($route);
				return;
			}
		}

		// Handle any unknown endpoints by redirecting to the 404 page
		self::handle404();
	}

	/**
	 * Parses the URL to determine the last part of the path.
	 *
	 * @param string $url The URL to parse.
	 * @return string The last part of the URL path.
	 */
	private function parseURL($currentURL)
	{
		$urlParts = explode('/', $currentURL);
		$lastURLpart = end($urlParts);

		return $lastURLpart;
	}

	/**
	 * Renders the 404 error template.
	 *
	 * @return void 
	 */
	private function handle404() {
		$this->templateRenderer->renderTemplate('_common/error/404', []);

		http_response_code(404);
		return;
	}

	/**
	 * Handles requests to the Session Controller.
	 *
	 * @param string $route The URL of the route that its being accessed.
	 */
	private function handleSessionController($route)
	{
		$sessionController = new SessionController($this->redisConfig);

		if ($route === 'login') {
			$sessionController->logIn();
		} else if ($route === 'logout') {
			$sessionController->logOut();
		} else {
			self::handle404();
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