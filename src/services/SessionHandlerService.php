<?php

namespace Services;

use Constants\Paths;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;

class SessionHandlerService
{
	private $SESSION_TTL = 1800; // 30 minutes
	protected $redisClient;

	private $templateRenderer;

	public function __construct(
		RedisService 	        $redisClient,
		TemplateRendererService $templateRenderer,
		array                   $sessionConfig
	)
	{
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;

		if (isset($sessionConfig['SESSION_TTL'])) {
			$this->SESSION_TTL = $sessionConfig['SESSION_TTL'];
		}
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
			RedirectionHelper::doRedirection(Paths::DASHBOARD);
		} else {
			$this->createSession();
			RedirectionHelper::doRedirection(Paths::DASHBOARD);
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

		// Redis session keys
		$redisSessionDataKeys = [
			'session_key',
			'currentChannelDetails',
			'currentTourDetails',
			'currentChannelID',
			'currentChannelName',
			'currentBookingComponentDetails',
			'currentBookingDetails',
			'currentBookingID',
			'bookingConfirmationDetails',
			'bookingConfirmationData',
			'tourConfirmationData',
			'currentTourID',
			'currentTourBookingDetails',
			'currentSelectedComponentKey',
			'componentFetchAttempted',
			'departurePickAttempted',
			'currentCustomerID',
			'customerIDsubmitted',
			'currentCustomersDetails',
			'customerDetailsSubmitted',
			'currentTemporaryBookingKey',
			'bookingIDsubmitted'
		];

		// Purge all stored data in Redis for each session key
		foreach ($redisSessionDataKeys as $key) {
			$this->redisClient->deleteItemFromRedis($key, RedisInstanceHelper::REDIS_TYPE_STRING);
		}

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
	 * Creates a new session if the current session is invalid or expired.
	 *
	 * This method checks if the session is still valid based on the time-to-live (TTL)
	 * and creates a new session if necessary.
	 *
	 * @return void
	 */
	private function createSession(): void {
		// Check if the session is still valid
		if (time() - $this->redisClient->getItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING) < $this->SESSION_TTL) {
			return;
		}
		
		// Session expired, clear the session key
		$this->redisClient->deleteItemFromRedis('session_key', RedisInstanceHelper::REDIS_TYPE_STRING);

		// Store a unique session key in Redis
		session_start();
		$session_id = session_id();
		$this->redisClient->storeItemInRedis('session_key', $session_id, RedisInstanceHelper::REDIS_TYPE_STRING);
	}

}