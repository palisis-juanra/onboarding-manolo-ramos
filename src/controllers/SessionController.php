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

		$this->templateRenderer = new TemplateRendererService();

		session_start();
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
		$this->templateRenderer->renderTemplate('_common/logoutPage', []);
	}

	private function redirectLogin()
	{
		header('Location: /login');
		exit;
	}

	public function createSessionObject() {

	}
}