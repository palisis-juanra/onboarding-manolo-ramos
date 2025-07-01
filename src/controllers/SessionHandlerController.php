<?php

namespace Controllers;

use Services\RedisService;
use Services\SessionHandlerService;
use Services\TemplateRendererService;

class SessionHandlerController 
{
	private $sessionHandler;

	public function __construct(
		RedisService $redisClient,
		TemplateRendererService $templateRenderer,
		array $sessionConfig
	)
	{
		$this->sessionHandler = new SessionHandlerService(
			$redisClient,
			$templateRenderer,
			$sessionConfig
		);	
	}
	
	public function logIn(): void 
	{
		$this->sessionHandler->handleLogIn();
	}

	public function logOut(): void
	{
		$this->sessionHandler->handleLogOut();
	}

	public function checkIfSessionIsActive(): bool
	{
		return $this->sessionHandler->checkIfSessionIsActive();
	}
}