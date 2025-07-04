<?php

namespace Services;

use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use SimpleXMLElement;
use TourCMS\Utils\TourCMS;

class TourViewHandlerService 
{
	// Instances
	private $tourCMSclient; 
	private $redisClient; 
	private $templateRenderer;
	private $errorHandler;

	private $tourTemplateData;

	private $currentChannelDetails;
	private $currentTourID;

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

		// Retrieve current channel & current tour details
		$this->currentChannelDetails = json_decode(
			$this->redisClient->getItemFromRedis(
				'currentChannelDetails', 
				RedisInstanceHelper::REDIS_TYPE_STRING
			)
		, true);

		$this->currentTourID = $this->redisClient->getItemFromRedis(
			'currentTourID', 
			RedisInstanceHelper::REDIS_TYPE_STRING
		);
	}

	public function renderTourViewPage(): void
	{
		$this->retrieveTourDetails();

		$this->templateRenderer->renderTemplate(
			'tourView/tourViewPage',
			[
				'tourTemplateData' => $this->tourTemplateData,
				'tourName' => $this->tourTemplateData['tourName'],
				'channelName' => $this->currentChannelDetails['channelName'],
				'channelLogo' => $this->currentChannelDetails['channelLogo'],
			] 
		);
	}

	private function retrieveTourDetails(): void
	{
		$channelID = $this->currentChannelDetails['channelID'];
		$tourID = $this->currentTourID;

		if ($channelID && $tourID) {
			$retrievedTourDetails = $this->tourCMSclient->show_tour(
				$tourID,
				$channelID
			);

			$this->buildTourDetailsTemplateData($retrievedTourDetails);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('SESS_NO_CHANNEL_OR_TOUR_ID')
			);
		}
	}

	private function buildTourDetailsTemplateData(SimpleXMLElement $tourDetails): void
	{
		if (isset($tourDetails->tour)) {
			foreach($tourDetails->tour as $details) {
				$this->tourTemplateData = [
					'tourID' => $details->tour_id,
					'tourName' => $details->tour_name,
					'tourCode' => $details->tour_code,
					'tourNextBookableDate' => $details->next_bookable_date,
					'tourLastBookableDate' => $details->last_bookable_date,
					'tourImage' => $details->images->image->url,
					'tourStartTime' => $details->start_time,
					'tourEndTime' => $details->end_time,
					'tourSummary' => $details->summary,
					'tourShortDesc' => $details->shortdesc,
					'tourPrice' => html_entity_decode($details->from_price_display),
					'bookingDataPeople' => $details->new_booking->people_selection->rate,
					'bookingDataDates' => $details->new_booking->date_selection
				];
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_TOUR_DATA')
			);
		}
	}
}