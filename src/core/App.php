<?php

namespace Core;

use Services\TemplateRenderService;

class App
{
	private $routes = [
		'/login' => [
			[
				'controller' => 'LoginController',
				'method' => 'POST',
				'action' => 'login',
			],
			[
				'controller' => 'LoginController',
				'method' => 'GET',
				'action' => 'loginPage',
			],
		],
		'/logout' => [
			[
				'controller' => 'LoginController',
				'method' => 'GET',
				'action' => 'logout',
			],
		]
	];

	public function __construct()
	{
		// Initialize any necessary components or services here
	}

	public function run()
	{
		// Call the controller -> use the template service
		$templateRender = new TemplateRenderService();
		$templateRender->renderTemplate('login/loginPage', []);
	}

	private function isLoggedIn(): bool
	{
		// Check redis
		return true;
	}

}