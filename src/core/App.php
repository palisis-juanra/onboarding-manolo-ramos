<?php

namespace Core;

use Controllers\ChannelListController;
use Core\Router;
use Controllers\SessionController;
use Services\TourCMSClientService;
use Services\TemplateRendererService;
class App
{
	private $router;

	// Services
	private $tourCMSclient;
	private $templateRenderer;

	// Controllers
	private $sessionController;
	private $channelListController;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms']);
		$this->templateRenderer = TemplateRendererService::getInstance();

		// Initialize Controllers
		$this->sessionController = new SessionController(
			$envConfig['redis'],
			$this->templateRenderer
		);
		// Initialize the Channel List Controller
		$this->channelListController = new ChannelListController(
			$this->tourCMSclient,
			$this->templateRenderer
		);

		// Initialize Router
		$this->router = new Router(
			$this->sessionController,
			$this->channelListController,
			$this->templateRenderer
		);
	}

	public function run()
	{
		$this->router->dispatch();
	}
}