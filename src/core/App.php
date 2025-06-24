<?php

namespace Core;

use Core\Router;
use Services\TourCMSClientService;
use Services\TemplateRendererService;
class App
{
	private $router;
	private $tourCMSclient;
	private $templateRenderer;

	public function __construct(array $envConfig)
	{
		// Init core Service instances
		$this->tourCMSclient = TourCMSClientService::getInstance($envConfig['tourcms']);
		$this->templateRenderer = new TemplateRendererService();

		$this->router = new Router(
			$this->tourCMSclient,
			$this->templateRenderer,
			$envConfig['redis']
		);
	}

	public function run()
	{
		$this->router->dispatch();
	}
}