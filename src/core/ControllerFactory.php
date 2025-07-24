<?php

namespace Core;

use Controllers\BookingHandlerController;
use Controllers\BookingListController;
use Controllers\ChannelListController;
use Controllers\CustomerEditController;
use Controllers\CustomerListController;
use Controllers\CustomerViewController;
use Controllers\LoginHandlerController;
use Controllers\TourListController;
use Controllers\TourViewController;

class ControllerFactory 
{
	public const LOGIN_HANDLER_CONTROLLER = 'loginController';
	public const CHANNEL_LIST_CONTROLLER = 'channelListController';
	public const TOUR_LIST_CONTROLLER = 'tourListController';
	public const TOUR_VIEW_CONTROLLER = 'tourViewController';
	public const BOOKING_HANDLER_CONTROLLER = 'bookingController';
	public const BOOKING_LIST_CONTROLLER = 'bookingListController';
	public const CUSTOMER_LIST_CONTROLLER = 'customerListController';
	public const CUSTOMER_VIEW_CONTROLLER = 'customerViewController';

	private array $controllerDependencies;

	public function __construct(array $controllerDependencies) 
	{
		$this->controllerDependencies = $controllerDependencies;
	}

	/**
	 * Creates a controller based on the controllerName received.
	 *
	 * @return object an instance of the matched controller
	 */
	public function create(string $controllerName): object
	{
		// Return already pre-instantiated controllers if available
		if (
			isset($this->controllerDependencies[$controllerName]) &&
			is_object($this->controllerDependencies[$controllerName])
		) {
			return $this->controllerDependencies[$controllerName];
		}

		return match ($controllerName) {
			self::LOGIN_HANDLER_CONTROLLER => new LoginHandlerController(
				$this->controllerDependencies['templateRenderer']
			),
			self::CHANNEL_LIST_CONTROLLER => new ChannelListController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::TOUR_LIST_CONTROLLER => new TourListController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::TOUR_VIEW_CONTROLLER => new TourViewController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::BOOKING_HANDLER_CONTROLLER => new BookingHandlerController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::BOOKING_LIST_CONTROLLER => new BookingListController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::CUSTOMER_LIST_CONTROLLER => new CustomerListController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::CUSTOMER_VIEW_CONTROLLER => new CustomerViewController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			self::CUSTOMER_EDIT_CONTROLLER => new CustomerEditController(
				$this->controllerDependencies['tourCMS'],
				$this->controllerDependencies['redisClient'],
				$this->controllerDependencies['templateRenderer'],
				$this->controllerDependencies['errorHandler']
			),
			default => throw new \Exception("Controller '$controllerName' not found.")
		};
	}
}