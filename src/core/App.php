<?php

namespace Core;

use Controllers\ErrorHandlerController;
use Core\Router;
use Controllers\SessionHandlerController;
use Services\TourCMSClientService;
use Services\TemplateRendererService;
use Helpers\RedisInstanceHelper;
class App
{
	// Core
	private $router;
	private $controllerFactory;

	// Services
	private $tourCMSclient;
	private $templateRenderer;
	private $redisClient;

	// Controllers
	private $sessionHandlerController;
	private $errorHandlerController;
	private $controllerInstance;

	// Factory dependencies
	private array $controllerDependencies;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms'])->getTourCMS();
		$this->templateRenderer = TemplateRendererService::getInstance($envConfig['project']);
		$this->redisClient = RedisInstanceHelper::getInstance($envConfig['redis']);

		// Initialize Session Handler
		$this->sessionHandlerController = new SessionHandlerController(
			$this->redisClient,
			$this->templateRenderer
		);

		// Initialize Error Handler
		$this->errorHandlerController = new ErrorHandlerController(
			$this->templateRenderer
		);

		// Build the controller dependencies array
		$this->controllerDependencies = [
			'tourCMS' => $this->tourCMSclient,
			'templateRenderer' => $this->templateRenderer,
			'errorHandler' => $this->errorHandlerController,
			'redisClient' => $this->redisClient
		];

		// Pass on the controller dependencies to the controller factory 
		$this->controllerFactory = new ControllerFactory($this->controllerDependencies);

		// Initialize Router
		$this->router = new Router(
			$this->sessionHandlerController,
			$this->errorHandlerController
		);
	}

	public function run()
	{
		// Evaluate the route that is currently being accessed.
		$this->router->dispatch();
		// Get the current route details (method, controller to be called, action...)
		$routeInfo = $this->router->getRouteDetails();
		
		// If routeInfo is provided as empty, show the error page
		if (empty($routeInfo)){
			return;
		}

		// Check if routeInfo contains valid route data
		if (isset($routeInfo['controller']) && isset($routeInfo['action'])) {
			// Generate an instance of the controller asocciated to that route
			$this->controllerInstance = $this->controllerFactory->create($routeInfo['controller']);
			
			// Run the associated function 
			$this->controllerInstance->{$routeInfo['action']}();
		} else {
			// Load error template
			$this->errorHandlerController->index();
		}
	}
}