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

	public function renderBookingListPage(): void
	{

		if (empty($this->currentChannelDetails['channelID'])) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);

			exit;
		}

		// Check if the booking ID query has been submitted
		$bookingIDsubmitted = $this->redisClient->getItemFromRedis(
		'bookingIDsubmitted',
		RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		// Render just the search form if no data has not been submitted
		if (!$bookingIDsubmitted) {
			$this->templateRenderer->renderTemplate(
				Templates::BOOKINGS_LIST,
				[
					'bookingIDsubmitted' => $bookingIDsubmitted
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
					'bookingIDsubmitted' => $bookingIDsubmitted
				]
			);

			$this->redisClient->deleteItemFromRedis(
				'bookingIDsubmitted',
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_BOOKINGS_DATA)
			);
		}
	}

	public function deleteBooking(): void
	{

	}

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
				'bookingIDsubmitted',
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

	private function buildBookingTemplateData(SimpleXMLElement $bookingResult): void
	{
		if (empty($bookingResult->booking) || $bookingResult->error != 'OK') {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_BOOKINGS_DATA)
			);
			return;
		}

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