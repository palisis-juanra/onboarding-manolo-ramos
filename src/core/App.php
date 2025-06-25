<?php

namespace Core;

use Controllers\ChannelListController;
use Core\Router;
use Services\SessionHandlerService;
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
	private $sessionHandlerService;
	private $channelListController;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms'])->getTourCMS();
		$this->templateRenderer = TemplateRendererService::getInstance();
		$this->redisClient = RedisInstanceHelper::getInstance($envConfig['redis']);

		// Initialize Controllers
		$this->sessionHandlerService = new SessionHandlerService(
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
			$this->sessionHandlerService,
			$this->channelListController,
			$this->templateRenderer
		);
	}

	public function run()
	{
		$this->router->dispatch();
	}
}