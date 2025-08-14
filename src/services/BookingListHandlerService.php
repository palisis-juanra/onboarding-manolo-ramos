<?php

namespace Services;

use Constants\ErrorCodes;
use Constants\Paths;
use Constants\Templates;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use TourCMS\Utils\TourCMS;
use SimpleXMLElement;

class BookingListHandlerService
{
	private $tourCMSclient;
	private $redisClient;
	private $templateRenderer;
	private $errorHandler;

	private $bookingTemplateData;
	private $currentChannelDetails;

	public function __construct(
		TourCMS 				$tourCMSclient,
		RedisService 			$redisClient,
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler
	)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
		$this->errorHandler = $errorHandler;

		$this->currentChannelDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentChannelDetails',
				RedisInstanceHelper::REDIS_TYPE_STRING
			), true);
	}

	/**
	 * Renders the booking list page.
	 *
	 * This method retrieves the bookings and renders the booking list page.
	 *
	 * @return void
	 */
	public function renderBookingListPage(): void
	{
		if (empty($this->currentChannelDetails['channelID'])) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);

			exit;
		}

		// Check if the booking ID query has been submitted
		$isBookingIDsubmitted = $this->redisClient->getItemFromRedis(
		'isBookingIDsubmitted',
		RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		// Render just the search form if no data has not been submitted
		if (!$isBookingIDsubmitted) {
			$this->templateRenderer->renderTemplate(
				Templates::BOOKINGS_LIST,
				[
					'isBookingIDsubmitted' => $isBookingIDsubmitted
				]
			);

			exit;
		}

		$this->retrieveBookings();

		if (!empty($this->bookingTemplateData)) {
			$this->templateRenderer->renderTemplate(
				Templates::BOOKINGS_LIST,
				[
					'bookingTemplateData' => $this->bookingTemplateData,
					'isBookingIDsubmitted' => $isBookingIDsubmitted
				]
			);

			$this->redisClient->deleteItemFromRedis(
				'isBookingIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_BOOKINGS_DATA)
			);
		}
	}

	/**
	 * Submits the booking ID for searching.
	 *
	 * This method retrieves the booking ID from the POST request and stores it in Redis.
	 * It then redirects to the bookings page.
	 *
	 * @return void
	 */
	public function submitBookingID(): void
	{
		if ($_POST['bookingID']) {
			$bookingID = $_POST['bookingID'];
			// TODO: check if this is needed
			$this->redisClient->storeItemInRedis(
				'currentBookingID',
				$bookingID,
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			// Save a flag to indicate that the booking ID search has been submitted
			$this->redisClient->storeItemInRedis(
				'isBookingIDsubmitted',
				'true',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::BOOKINGS);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_BOOKING_ID)
			);
		}
	}

	/**
	 * Cancels the booking by ID.
	 *
	 * This method retrieves the current booking ID from Redis and checks if it is valid.
	 * If valid, it redirects to the booking cancellation page.
	 *
	 * @return void
	 */
	public function submitCancelledBookingID(): void
	{
		$currentBookingID = $this->redisClient->getItemFromRedis(
			'currentBookingID',
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		if (empty($currentBookingID)) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_BOOKING_ID)
			);
		}
		
		if (!empty($_POST['bookingCancellationID'])) {
			if ($_POST['bookingCancellationID'] !== $currentBookingID) {
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_MATCHING_STORED_BOOKING_ID)
				);

			} else {
				$currentBookingID = $_POST['bookingCancellationID'];
			}
		}

		$bookingCancellationTemplateData = [
			'bookingID' => $currentBookingID
		];

		$this->redisClient->storeItemInRedis(
			'bookingCancellationData',
			json_encode($bookingCancellationTemplateData),
			RedisInstanceHelper::REDIS_TYPE_STRING
		);

		RedirectionHelper::doRedirection(Paths::BOOKING_CANCELLATION);
	}

	/**
	 * Retrieves the bookings from TourCMS.
	 *
	 * This method fetches the current booking ID from Redis and retrieves the booking details
	 * from TourCMS using the channel ID stored in the session.
	 *
	 * @return void
	 */
	private function retrieveBookings(): void
	{
		if ($this->currentChannelDetails['channelID']) {
			$currentBookingID = $this->redisClient->getItemFromRedis(
				'currentBookingID',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			// TODO: check if the channel ID matches the one associated to the booking, throw incorrect booking id or channelID
			$bookingResult =$this->tourCMSclient->show_booking($currentBookingID, $this->currentChannelDetails['channelID']);

			$this->buildBookingTemplateData($bookingResult);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);
		}
	}

	/**
	 * Builds the booking template data from the booking result XML.
	 *
	 * @param SimpleXMLElement $bookingResult The booking result XML.
	 * @return void
	 */
	private function buildBookingTemplateData(SimpleXMLElement $bookingResult): void
	{
		if (empty($bookingResult->booking) || $bookingResult->error != 'OK') {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_BOOKINGS_DATA)
			);
			return;
		}

		// TODO: add random image generation
		$booking = $bookingResult->booking;

		$this->bookingTemplateData[] = [
			'bookingID'     => (string) ($booking->booking_id ?? ''),
			'bookingName'   => (string) ($booking->booking_name ?? ''),
			'channelID'     => (string) ($booking->channel_id ?? ''),
			'channelName'   => (string) ($booking->channel_name ?? ''),
			'startDate'     => (string) ($booking->start_date ?? ''),
			'endDate'       => (string) ($booking->end_date ?? ''),
			'status'        => (string) ($booking->status ?? ''),
			'statusText'    => (string) ($booking->status_text ?? ''),
			'customerCount' => (string) ($booking->customer_count ?? ''),
			'customerData'  => $booking->customers ?? [],
		];
	}
}