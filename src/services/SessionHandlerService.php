<?php

namespace Services;

use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;

class SessionHandlerService
{
	protected const SESSION_TTL = 1800; // 30 minutes
	protected $redisClient;

	private $templateRenderer;

	public function __construct(
		RedisService 	$redisClient,
		TemplateRendererService $templateRenderer
	)
	{
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
	}

	/**
	 * Handles the login request.
	 *
	 * This method processes the login request and redirects 
	 * to the appropiate page depending on the outcome.
	 *
	 * @return bool
	 */
	public function handleLogIn(): void
	{
		if ($this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING)) {
			// If a session key exists, redirect to the home page or dashboard
			RedirectionHelper::headerRedirection('/dashboard/');
		} else {
			$this->createSession();
			RedirectionHelper::headerRedirection('/dashboard/');
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
		// If a PHP session is active, unset
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_unset(); // Clear session variables
		}

		// Unset the session key in Redis
		$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);
		
		$this->templateRenderer->renderTemplate('_common/logoutPage', []);
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
		// Check if the session key exists in Redis
		return $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) !== null;
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
		$this->templateRenderer->renderTemplate('login/loginPage', []);
	}

	/**
	 * Renders the 404 error page.
	 *
	 * @return void
	 */
	public function renderNotFoundPage(): void
	{
		$this->templateRenderer->renderTemplate('_common/error/404', []);
		http_response_code(404);
		return;
	}

	/**
	 * Creates a new session if the current session is invalid or expired.
	 *
	 * This method checks if the session is still valid based on the time-to-live (TTL)
	 * and creates a new session if necessary.
	 *
	 * @return void
	 */
	private function createSession(): void {
		// Check if the session is still valid
		if (time() - $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) < self::SESSION_TTL) {
			return; // Session is still valid, no need to create a new one
		}
		
		// Session expired, clear the session key
		$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);

		// Store a unique session key in Redis
		$session_id = session_id();
		$this->redisClient->storeItemInRedis('session_key', $session_id, RedisInstanceHelper::REDIS_TYPE_STRING);
	}

}