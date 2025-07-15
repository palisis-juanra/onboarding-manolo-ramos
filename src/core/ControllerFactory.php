<?php

namespace Core;

use Controllers\BookingHandlerController;
use Controllers\BookingListController;
use Controllers\ChannelListController;
use Controllers\LoginHandlerController;
use Controllers\TourListController;
use Controllers\TourViewController;

class ControllerFactory 
{
	// Controller names 
	public const LOGIN_HANDLER_CONTROLLER = 'loginController';
	public const CHANNEL_LIST_CONTROLLER = 'channelListController';
	public const TOUR_LIST_CONTROLLER = 'tourListController';
	public const TOUR_VIEW_CONTROLLER = 'tourViewController';
	public const BOOKING_HANDLER_CONTROLLER = 'bookingController';
	public const BOOKING_LIST_CONTROLLER = 'bookingListController';

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
	public function create(string $controllerName): object
	{
		// Return pre-instantiated controller if available
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
			self::CHANNEL_LIST_CONTROLLER,
			self::TOUR_LIST_CONTROLLER,
			self::TOUR_VIEW_CONTROLLER,
			self::BOOKING_HANDLER_CONTROLLER,
			self::BOOKING_LIST_CONTROLLER => match ($controllerName) {
				self::CHANNEL_LIST_CONTROLLER => new ChannelListController(...$this->controllerDependencies),
				self::TOUR_LIST_CONTROLLER => new TourListController(...$this->controllerDependencies),
				self::TOUR_VIEW_CONTROLLER => new TourViewController(...$this->controllerDependencies),
				self::BOOKING_HANDLER_CONTROLLER => new BookingHandlerController(...$this->controllerDependencies),
				self::BOOKING_LIST_CONTROLLER => new BookingListController(...$this->controllerDependencies),
			},
			default => throw new \Exception("Controller '$controllerName' not found."),
		};
	}
}