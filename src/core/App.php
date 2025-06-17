<?php

namespace Core;

use Core\Router;
class App
{
	private $router;

	public function __construct()
	{
		$this->router = new Router();
	}

	public function run()
	{
		$this->router->dispatch();
	}

	private function isLoggedIn(): bool
	{
		// Check redis
		return true;
	}

}