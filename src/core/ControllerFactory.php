<?php

namespace Core;

use Controllers\ChannelListController;
use Controllers\ErrorHandlerController;
use Controllers\LoginHandlerController;
use Controllers\SessionHandlerController;

class ControllerFactory 
{
	// Controller names 
	public const LOGIN_HANDLER_CONTROLLER = 'SessionHandler';
	public const SESSION_HANDLER_CONTROLLER = 'SessionHandler';
	public const ERROR_HANDLER_CONTROLLER = 'ErrorHandler';
	public const CHANNEL_LIST_CONTROLLER = 'ChannelList';
		
	// Config passed on to the Controller instance
	private $controllerDependencies;

	public function __construct(array $controllerDependencies) 
	{
		$this->controllerDependencies = $controllerDependencies;
	}

	/**
	 * Creates a controller based on the controllerName received.
	 *
	 * @return object an instance of the matched controller
	 */
	public function create(string $controllerName)
	{
		switch ($controllerName) {
			case self::LOGIN_HANDLER_CONTROLLER:
				return new LoginHandlerController(
					$this->controllerDependencies['templateRenderer'],
				);
			case self::SESSION_HANDLER_CONTROLLER:
				return new SessionHandlerController(
					$this->controllerDependencies['redisClient'],
					$this->controllerDependencies['templateRenderer'],
				);
			case self::CHANNEL_LIST_CONTROLLER:
				return new ChannelListController(
					$this->controllerDependencies['tourCMS'],
					$this->controllerDependencies['redisClient'],
					$this->controllerDependencies['templateRenderer'],
				);
			case self::ERROR_HANDLER_CONTROLLER:
				return new ErrorHandlerController(
					$this->controllerDependencies['templateRenderer']
				);
			default:
				throw new \Exception("Controller '$controllerName' not found.");
		}
	}
}