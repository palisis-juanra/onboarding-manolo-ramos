<?php

namespace Controllers;

use Services\RedisService;
use Services\SessionHandlerService;
use Services\TemplateRendererService;

class SessionHandlerController 
{
	private $sessionHandlerService;

	public function __construct(
		RedisService $redisClient,
		TemplateRendererService $templateRenderer
	)
	{
		$this->sessionHandlerService = new SessionHandlerService(
			$redisClient,
			$templateRenderer
		);	
	}
	
	public function logIn(): void 
	{
		$this->sessionHandlerService->handleLogIn();
	}

	public function logOut(): void
	{
		$this->sessionHandlerService->handleLogOut();
	}

	public function checkIfSessionIsActive(): bool
	{
		return $this->sessionHandlerService->checkIfSessionIsActive();
	}
}