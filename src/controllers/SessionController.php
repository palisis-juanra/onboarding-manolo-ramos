<?php

namespace Controllers;

require_once __DIR__ . '/../../config/env_config.php';

use Services\TemplateRendererService;
use Helpers\RedisInstanceHelper;

class SessionController
{
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

		// Start the session or create a new one if it doesn't exist
		self::createSession();

		$this->templateRenderer = new TemplateRendererService();
	}
	
	private function createSession() {
		if ($this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING)) {
			// If session key exists, check if the session is still valid
			if (time() - $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) < self::SESSION_TTL) {
				// Session is valid, continue
				session_start();
				return;
			} else {
				// Session expired, clear the session key
				$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);
			}
		}

		// Store an unique session key in Redis
		$this->redisClient->storeItemInRedis('session_key', bin2hex(random_bytes(16)), RedisInstanceHelper::REDIS_TYPE_STRING);
	}

	// TODO: redis, redir if logged, handle submit
	/**
	 * Handles the login request.
	 *
	 * This method processes the login request, validates the credentials,
	 * and returns a response indicating success or failure.
	 *
	 * @return void
	 */
	public function logIn()
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
	public function logOut()
	{
		//TODO: Implement the logout logic here
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
		header('Location: /login');
		exit;
	}

}