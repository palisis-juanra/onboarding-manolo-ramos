<?php

namespace Core;

use Controllers\ChannelListController;
use Core\Router;
use Controllers\SessionController;
use Services\TourCMSClientService;
use Services\TemplateRendererService;
use Helpers\RedisInstanceHelper;
class App
{
	private $router;

	// Services
	private $tourCMSclient;
	private $templateRenderer;
	private $redisClient;

	// Controllers
	private $sessionController;
	private $channelListController;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms']);
		$this->templateRenderer = TemplateRendererService::getInstance();
		$this->redisClient = RedisInstanceHelper::getInstance($envConfig['redis']);

		// Initialize Controllers
		$this->sessionController = new SessionController(
			$this->redisClient,
			$this->templateRenderer
		);
		// Initialize the Channel List Controller
		$this->channelListController = new ChannelListController(
			$this->tourCMSclient,
			$this->redisClient,
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