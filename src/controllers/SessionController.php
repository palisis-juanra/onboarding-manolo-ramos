<?php

namespace Controllers;

require_once __DIR__ . '/../../config/env_config.php';

use Services\SessionHandlerService;

class SessionController
{
	// Service instances
	protected $sessionHandlerService;

	public function __construct(array $redisConfig)
	{
		$this->sessionHandlerService = new SessionHandlerService($redisConfig);
	}

	/**
	 * Handles the login request.
	 *
	 * This method processes the login request and redirects 
	 * to the appropiate page depending on the outcome.
	 *
	 * @return void
	 */
	public function handleLogIn(): void
	{
		if ($this->sessionHandlerService->handleLogIn()) {
			// If a session key exists, redirect to the home page or dashboard
			header('Location: /onboarding-manolo-ramos/');
			exit;
		} else {
			// Redirect to the dashboard after creating a new session
			header('Location: /dashboard/');
			exit;
		}
	}

	/**
	 * Handles the logout request.
	 *
	 * This method processes the logout request, clears the session,
	 * and returns a response indicating successful logout.
	 *
	 * @return void
	 */
	public function handleLogOut(): void
	{
		$this->sessionHandlerService->handleLogOut();
	}

	/**
	 * Checks if the current session is active.
	 *
	 * This method checks if the session key exists in Redis to determine
	 * if the session is active.
	 *
	 * @return bool True if the session is active, false otherwise.
	 */
	public function checkIfSessionIsActive(): bool
	{
		return $this->sessionHandlerService->checkIfSessionIsActive();
	}

	/**
	 * Renders the login page.
	 *
	 * This method renders the login page template.
	 *
	 * @return void
	 */
	public function renderLoginPage(): void
	{
		$this->sessionHandlerService->renderLoginPage();
	}
}