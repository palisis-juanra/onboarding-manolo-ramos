<?php

namespace Core;

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
	private $controllerInstance;

	// Factory dependencies
	private array $controllerDependencies;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms'])->getTourCMS();
		$this->templateRenderer = TemplateRendererService::getInstance();
		$this->redisClient = RedisInstanceHelper::getInstance($envConfig['redis']);

		// Initialize Session Handler
		$this->sessionHandlerController = new SessionHandlerController(
			$this->redisClient,
			$this->templateRenderer
		);

		// Build the controller dependencies collection
		$this->controllerDependencies = [
			'tourCMS' => $this->tourCMSclient,
			'templateRenderer' => $this->templateRenderer,
			'redisClient' => $this->redisClient
		];

		// Pass on the controller dependencies to the controller factory 
		$this->controllerFactory = new ControllerFactory($this->controllerDependencies);

		// Initialize Router
		$this->router = new Router(
			$this->sessionHandlerController,
			$this->templateRenderer
		);
	}

	public function run()
	{
		// Evaluate the route that is currently beign accessed.
		$this->router->dispatch();
		$routeInfo = $this->router->getRouteInfo();
		// Check if there's an active session or if the current route doesn't require login
		if ($this->sessionHandlerController->checkIfSessionIsActive() || !$routeInfo['requiresLogin']) {
			$this->controllerInstance = $this->controllerFactory->create($routeInfo['controller']);
			
			// Run the associated function 
			$this->controllerInstance->{$routeInfo['action']}();
		} else {
			// Load error template
			$sessionController = $this->controllerFactory->create('SessionHandler');
			$sessionController->renderNotFoundPage();
		}
	}
}