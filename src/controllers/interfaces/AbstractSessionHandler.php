<?php

namespace Controllers\interfaces;

abstract class AbstractSessionHandler
{
	protected const SESSION_TTL = 0;

	/**
	 * Handles the login request.
	 *
	 * This method processes the login request, validates the credentials,
	 * and returns a response indicating success or failure.
	 *
	 * @return void
	 */
	public function handleLogIn(): void {}

	/**
	 * Handles the logout request.
	 *
	 * This method processes the logout request, clears the session,
	 * and returns a response indicating successful logout.
	 *
	 * @return void
	 */
	public function handleLogOut(): void {}

	/**
	 * Creates a new session if the current session is invalid or expired.
	 *
	 * This method checks if the session is still valid based on the time-to-live (TTL)
	 * and creates a new session if necessary.
	 *
	 * @return void
	 */
	private function createSession(): void {}

	/**
	 * Checks if the current session is active
	 */
	public function checkIfSessionIsActive(): bool {}

	/**
	 * Renders the login page.
	 *
	 * This method renders the login page template.
	 *
	 * @return void
	 */
	public function renderLoginPage(): void {}
}