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
			'sessionHandler' => $this->sessionHandlerController,
			'errorHandler' => $this->errorHandlerController,
			'tourCMS' => $this->tourCMSclient,
			'templateRenderer' => $this->templateRenderer,
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

	// Serves as the main entrypoint for the application
	public function run()
	{
		// Evaluate the route that is currently being accessed and retrieve its details
		$this->router->dispatch();
		$routeInfo = $this->router->getRouteDetails();
		
		// If routeInfo is provided as empty or there's no valid controller reference, show the error page
		if (empty($routeInfo) || empty($routeInfo['controller'])){
			return;
		}

		try {
			// Generate an instance of the controller asocciated to that route
			$this->controllerInstance = $this->controllerFactory->create($routeInfo['controller']);

			if (
				isset($routeInfo['action']) &&
				method_exists($this->controllerInstance, $routeInfo['action'])
			) {
				// Run the associated function 
				$this->controllerInstance->{$routeInfo['action']}();
			} else {
				$this->errorHandlerController->index('');
			}
		} catch (\Exception $e) {
			$this->errorHandlerController->index($e);
		}
	}
}