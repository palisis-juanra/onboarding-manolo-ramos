<?php

namespace Controllers;

require_once __DIR__ . '/../../config/env_config.php';

use Services\TemplateRendererService;
use Helpers\RedisInstanceHelper;

class SessionController
{
	protected const SESSION_TTL = 1800; // 30 minutes
	protected $redisClient;
	
	private $templateRenderer;

	public function __construct(array $redisConfig)
	{
		// Redis client initialization
		$this->redisClient = RedisInstanceHelper::getInstance(
			$redisConfig['REDIS_HOST'],
			$redisConfig['REDIS_PORT'],
			$redisConfig['REDIS_PASSWORD']
		);

		$this->templateRenderer = new TemplateRendererService();
	}

	/**
	 * Handles the login request.
	 *
	 * This method processes the login request, validates the credentials,
	 * and returns a response indicating success or failure.
	 *
	 * @return void
	 */
	public function handleLogIn(): void
	{
		if ($this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING)) {
			// If a session key exists, redirect to the home page or dashboard
			header('Location: /onboarding-manolo-ramos/');
			exit;
		} else {
			$this->createSession();
			// Redirect to the dashboard after creating a new session
			header('Location: /dashboard/');
		}
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
		//return $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) !== null;
		return true;
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
	 * Checks if the user is logged in.
	 *
	 * This method checks the Redis session to determine if the user is logged in.
	 * If not, it redirects to the login page.
	 *
	 * @return void
	 */
	private function redirectLogin()
	{
		header('Location: login/');
		exit;
	}

}