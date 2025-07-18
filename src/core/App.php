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

		$this->sessionHandlerController = new SessionHandlerController(
			$this->redisClient,
			$this->templateRenderer,
			$envConfig['session']
		);

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

		$this->controllerFactory = new ControllerFactory($this->controllerDependencies);

		$this->router = new Router(
			$this->sessionHandlerController,
			$this->errorHandlerController
		);
	}

	public function run()
	{
		// Evaluate the route that is currently being accessed and retrieve its details
		$this->router->dispatch();
		$routeInfo = $this->router->getRouteDetails();

		if (empty($routeInfo) || empty($routeInfo['controller'])){
			return;
		}

		try {
			// Generate an instance of the controller associated to that route
			$this->controllerInstance = $this->controllerFactory->create($routeInfo['controller']);

			if (
				isset($routeInfo['action']) &&
				method_exists($this->controllerInstance, $routeInfo['action'])
			) {
				// Run the associated function 
				$this->controllerInstance->{$routeInfo['action']}();
			} else {
				$this->errorHandlerController->index(
					$this->errorHandlerController->getErrorMessage('INCORRECT_ROUTE_ACTION')
				);
			}
		} catch (\Exception $e) {
			$this->errorHandlerController->index($e);
		}
	}
}