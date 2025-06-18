<?php

namespace Controllers;

require_once __DIR__ . '/../../config/env_config.php';

use Services\TemplateRendererService;
use Helpers\RedisInstanceHelper;

class SessionController
{
	// Instances
	private $templateRenderer;

	protected $redisClient;
	protected const SESSION_TTL = 1800; // 30 minutes

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
	public function handlelogIn()
	{
		if ($this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING)) {
			// If a session key exists, redirect to the home page or dashboard
			header('Location: /onboarding-manolo-ramos');
			exit;
		} else {
			$this->createSession();
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
	private function createSession() {
		// Check if the session is still valid
		if (time() - $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) < self::SESSION_TTL) {
			return; // Session is still valid, no need to create a new one
		} else {
			// Session expired, clear the session key
			$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);
		}

		// Store a unique session key in Redis
		$session_id = session_id();
		$this->redisClient->storeItemInRedis('session_key', $session_id, RedisInstanceHelper::REDIS_TYPE_STRING);
	}

	/**
	 * Renders the login page.
	 *
	 * This method renders the login page template.
	 *
	 * @return void
	 */
	public function renderLoginPage()
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
	public function handlelogOut()
	{
		// Clear the session key from Redis
		session_destroy();
		$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);

		// Redirect to the login page
		header('Location: login/');
		
		$this->templateRenderer->renderTemplate('_common/logoutPage', []);
	}

	public function checkActiveRedisSession(): bool
	{
		// TODO: The session controller must return a Redis flag to indicate if there's a session or not 
		return true;
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