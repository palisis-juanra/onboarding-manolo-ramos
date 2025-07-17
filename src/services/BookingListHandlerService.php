<?php

namespace Services;

use Constants\Paths;
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
		// Check if the booking ID query has been submitted
		$bookingIDsubmitted = $this->redisClient->getItemFromRedis(
		'bookingIDsubmitted',
		RedisInstanceHelper::REDIS_TYPE_STRING) === 'true';

		// Render just the search form if no data has not been submitted
		if (!$bookingIDsubmitted) {
			$this->templateRenderer->renderTemplate(
				'bookings/bookingsListPage',
				[
					'bookingIDsubmitted' => $bookingIDsubmitted
				]
			);

			exit;
		}

		$this->retrieveBookings();

		if (!empty($this->bookingTemplateData)) {
			$this->templateRenderer->renderTemplate(
				'bookings/bookingsListPage',
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
				$this->errorHandler->getErrorMessage('NO_BOOKINGS_DATA')
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
				$this->errorHandler->getErrorMessage('POST_NO_BOOKING_ID')
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

			// TODO: check if the channel ID matches the one associated to the booking, throw incorrect booking id or
			// channelID
			$bookingResult =$this->tourCMSclient->show_booking($currentBookingID, $this->currentChannelDetails['channelID']);

			$this->buildBookingTemplateData($bookingResult);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('SESS_NO_CHANNEL_ID')
			);
		}
	}

	private function buildBookingTemplateData(SimpleXMLElement $bookingResult): void
	{
		if (isset($bookingResult->booking) && $bookingResult->error == 'OK') {
			$this->bookingTemplateData[] = [
				'bookingID' => (string) $bookingResult->booking->booking_id ?? '',
				'bookingName' => (string) $bookingResult->booking->booking_name ?? '',
				'channelID' => (string) $bookingResult->booking->channel_id ?? '',
				'channelName' => (string) $bookingResult->booking->channel_name ?? '',
				'startDate' => (string) $bookingResult->booking->start_date ?? '',
				'endDate' => (string) $bookingResult->booking->end_date ?? '',
				'status' => (string) $bookingResult->booking->status ?? '',
				'statusText' => (string) $bookingResult->booking->status_text ?? '',
				'customerCount' => (string) $bookingResult->booking->customer_count ?? '',
				'customerData' => $bookingResult->booking->customers ?? []
			];
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_BOOKINGS_DATA')
			);
		}
	}

}