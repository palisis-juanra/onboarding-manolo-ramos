<?php

namespace Services;

use Controllers\ErrorHandlerController;
use Helpers\RedisInstanceHelper;
use TourCMS\Utils\TourCMS;

class TourListHandlerService 
{
	// Instances
	private $tourCMSclient; 
	private $redisClient; 
	private $templateRenderer;
	private $errorHandler;

	private $toursTemplateData;
	private $currentChannelDetails;

	private $totalTourCount;

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
			)
		, true);
	}

	public function renderTourListPage(): void
	{
		$this->retrieveTourList();

		$this->templateRenderer->renderTemplate(
			'tourList/tourListPage',
			[
				'toursTemplateData' => $this->toursTemplateData,
				'channelName' => $this->currentChannelDetails['channelName'],
				'channelLogo' => $this->currentChannelDetails['channelLogo'],
				'channelTourCount' => $this->totalTourCount
			]
		);
	}

	// TODO: move this function to a TourDetailsHandler class
	public function showTour(): void
	{
		
	}

	private function retrieveTourList(): void
	{
		$channelID = $this->currentChannelDetails['channelID'];

		$queryString = $this->buildQuery(30, 1, 0, 'PT');

		if ($channelID) {
			$retrievedTours = $this->tourCMSclient->search_tours($queryString, $channelID);
			$this->buildTourListTemplateData($retrievedTours);
		} else {
			// TODO: automatic redirection to dashboard page
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('SESS_NO_CHANNEL_ID')
			);
		}
	}

	private function buildTourListTemplateData(object $tourList): void
	{
		if (isset($tourList->tour)) {
			$this->totalTourCount = (string) $tourList->total_tour_count; 
			foreach($tourList->tour as $tour) {
				$this->toursTemplateData[] = [
					'tourID' => $tour->tour_id ?? '',
					'tourName' => $tour->tour_name ?? '',
					'location' => $tour->location ?? '',
					'tourCode' => $tour->tour_code ?? '',
					'shortDescription' => $tour->shortdesc ?? '',
					'thumbnailImage' => $tour->thumbnail_image ?? '',
					'hasSale' => $tour->has_sale ?? '',
					'lastUpdated' => $tour->descriptions_last_updated ?? '',
					'channelId' => $tour->channel_id ?? '',
					'fromPrice' => $tour->from_price_display ?? ''
				];
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_TOUR_DATA')
			);
		}
	}

	private function buildQuery(
		int $toursPerPage,
		int $currentPage,
		int  $productType,
		string $country
	) 
	{
		// Set a querystring for the search
		$queryParameters = [
			"per_page" => $toursPerPage,
			"page" => $currentPage
		];

		$queryString = http_build_query($queryParameters);

		return $queryString;
	}
}