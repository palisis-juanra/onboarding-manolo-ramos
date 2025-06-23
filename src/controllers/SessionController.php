<?php

namespace Controllers;

require_once __DIR__ . '/../../config/env_config.php';
require_once __DIR__ . '/interfaces/AbstractSessionHandler.php';

use Controllers\interfaces\AbstractSessionHandler;

class SessionController
{
	protected AbstractSessionHandler $sessionHandler;

	public function __construct(AbstractSessionHandler $sessionHandler)
	{
		$this->sessionHandler = $sessionHandler;
	}

	public function handleLogIn(): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			$this->sessionHandler->handleLogIn();
		}
	}

	public function handleLogOut(): void
	{
		if ($this->sessionHandler->checkIfSessionIsActive()) {
			$this->sessionHandler->handleLogOut();
		}
	}

	public function checkIfSessionIsActive(): bool
	{
		return $this->sessionHandler->checkIfSessionIsActive();
	}

	public function renderLoginPage(): void
	{
		$this->sessionHandler->renderLoginPage();
	}
}