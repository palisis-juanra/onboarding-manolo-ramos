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
		\TourCMS\Utils\TourCMS 	$tourCMSclient,
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

		if (empty($this->toursTemplateData)) {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CHANNEL_TOUR_DATA)
			);
			return;
		}

		$this->templateRenderer->renderTemplate(
			Templates::TOUR_LIST_PAGE,
			[
				'toursTemplateData' => $this->toursTemplateData,
				'channelName' => $this->currentChannelDetails['channelName'],
				'channelLogo' => $this->currentChannelDetails['channelLogo'],
				'channelTourCount' => $this->totalTourCount
			]
		);
	}

	public function submitTourPick(): void
	{
		if (isset($_POST['tourID'])) {
			$currentTourID = $_POST['tourID'];

			$this->redisClient->storeItemInRedis(
				'currentTourID', 
				$currentTourID, 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::TOUR_VIEW);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::POST_NO_TOUR_ID)
			);
		}
	}

	private function retrieveTourList(): void
	{
		$channelID = $this->currentChannelDetails['channelID'];

		// TODO: better explain this asignation using variables instead of direct values
		$queryString = $this->buildQuery(30, 1, 0, 'PT');

		if ($channelID) {
			$retrievedTours = $this->tourCMSclient->search_tours(
				$queryString,
				$channelID
			);
			$this->buildTourListTemplateData($retrievedTours);
		} else {
			// TODO: automatic redirection to dashboard page
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage(ErrorCodes::SESS_NO_CHANNEL_ID)
			);
		}
	}

	private function buildTourListTemplateData(SimpleXMLElement $tourList): void
	{
		if (isset($tourList->tour)) {
			$this->totalTourCount = (string) $tourList->total_tour_count; 
			foreach($tourList->tour as $tour) {
				$this->toursTemplateData[] = [
					'tourID' => $tour->tour_id ?? '',
					'tourName' => $tour->tour_name ?? '',
					'location' => $tour->location ? html_entity_decode($tour->location) : '',
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
				$this->errorHandler->getErrorMessage(ErrorCodes::NO_CHANNEL_TOUR_DATA)
			);
		}
	}

	// TODO: revisit this logic
	private function buildQuery(
		int     $toursPerPage,
		int     $currentPage,
		int     $productType,
		string  $country
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