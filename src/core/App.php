<?php

namespace Core;

use Core\Router;
class App
{
	private $router;
	protected $envConfig;

	public function __construct(array $envConfig)
	{
		$this->envConfig = $envConfig;
		$this->router = new Router($this->envConfig);
	}

	public function run()
	{
		$this->router->dispatch();
	}
}