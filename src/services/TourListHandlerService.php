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

	private $templateData;

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
	}

	public function renderTourListPage(): void
	{
		$this->retrieveTourList();
		$this->templateRenderer->renderTemplate('tourList/tourListPage', ['templateData' => $this->templateData]);
	}

	public function showTour(): void
	{
		
	}

	private function retrieveTourList(): void
	{
		$channelID = $this->redisClient->getItemFromRedis('currentChannelID', RedisInstanceHelper::REDIS_TYPE_STRING);

		if ($channelID) {
			$retrievedTours = $this->tourCMSclient->list_tours($channelID);
			$retrievedTourThumbnails = $this->tourCMSclient->list_tour_images($channelID);

			$this->buildTourListTemplateData($retrievedTourThumbnails, $retrievedTours);
		} else {
			// TODO: automatic redirection to dashboard page
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('SESS_NO_CHANNEL_ID')
			);
			//RedirectionHelper::doRedirection('/dashboard/');
		}
	}

	private function buildTourListTemplateData(object $tourImages, object $tourList): void
	{
		$mappedTourImages = [];

		// Process retrieved Tour Images
		if (isset($tourImages->tour)) {
			foreach ($tourImages->tour as $thumbnail){
				$tourID = (string) $thumbnail->tour_id;

				if(!isset($thumbnail->images->image->url_thumbnail)) {
					$mappedTourImages[$tourID] = 'https://picsum.photos/342/228';
				}

				$mappedTourImages[$tourID] = (string) $thumbnail->images->image->url_thumbnail;
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_TOUR_DATA')
			);
			return;
		}

		if (isset($tourList->tour)) {
			foreach($tourList->tour as $tour) {
				$tourID = (string) $tour->tour_id;
				$this->templateData[] = [
					'tourID' => $tourID ?? '',
					'tourName' => $tour->tour_name ?? '',
					'tourCode' => $tour->tour_code ?? '',
					'thumbnailImage' => $mappedTourImages[$tourID] ?? null,
					'hasSale' => $tour->has_sale ?? '',
					'lastUpdated' => $tour->descriptions_last_updated ?? '',
					'channelId' => $tour->channel_id ?? '',
				];
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_TOUR_DATA')
			);
			return;
		}
	}
}