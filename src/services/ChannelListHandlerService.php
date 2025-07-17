<?php

namespace Services;

use Constants\Paths;
use Controllers\ErrorHandlerController;
use Helpers\RedirectionHelper;
use Helpers\RedisInstanceHelper;
use TourCMS\Utils\TourCMS;

class ChannelListHandlerService 
{
	// Service instances
	private $tourCMSclient;
	private $redisClient;
	private $templateRenderer;
	private $errorHandler;

	// Member variables
	private $channelList;
	private array $templateData;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer,
		ErrorHandlerController 	$errorHandler,
	)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
		$this->errorHandler = $errorHandler;

		$this->channelList = $this->tourCMSclient->list_channels();
	}

	public function submitChannelPick(): void
	{
		if (isset($_POST['channelList'])) {
			$channelID = $_POST['channelList'];
			
			// Get channel name from matching channelID
			foreach ($this->channelList->channel as $channel => $channelDetails) {
				if ($channelDetails->channel_id == $channelID) {
					$isChannelMatched = true;

					$channelName = (string) $channelDetails->channel_name ?? '';
					$tourCount = (string) $channelDetails->tour_count ?? '';
					$channelLogo = (string) $channelDetails->logo_url ?? '';
				}
			}

			if (!$isChannelMatched) {
				$this->errorHandler->index(
					$this->errorHandler->getErrorMessage('POST_NO_CHANNEL_ID')
				);
			}

			$currentChannelDetails = [
				'channelID' => $channelID,
				'channelName' => $channelName,
				'tourCount' => $tourCount,
				'channelLogo' => $channelLogo
			];

			$this->redisClient->storeItemInRedis(
				'currentChannelDetails', 
				json_encode($currentChannelDetails), 
				RedisInstanceHelper::REDIS_TYPE_STRING
			);

			RedirectionHelper::doRedirection(Paths::TOUR_LIST);
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('POST_NO_CHANNEL_ID')
			);
		}
	}

	public function renderChannelListPage(): void
	{
		$this->buildChannelsTemplateData($this->channelList);

		$this->templateRenderer->renderTemplate('channels/channelListPage', ['templateData' => $this->templateData]);
	}
	
	private function buildChannelsTemplateData(\SimpleXMLElement $channelList): void
	{
		// TODO: remove unnecesary data from the array
		if (isset($channelList->channel)) {
			foreach($channelList->channel as $channel) {
				$this->templateData[] = [
					'channelID' => $channel->channel_id ?? '',
					'accountID' => $channel->account_id ?? '',
					'channelName' => $channel->channel_name ?? '',
					'tourCount' => $channel->tour_count ?? '',
					'logoURL' => $channel->logo_url ? $channel->logo_url : '',
					'connectionPermission' => $channel->connection_permission ?? '',
					'lang' => $channel->lang ?? '',
					'saleCurrency' => $channel->sale_currency ?? '',
					'homeURL' => $channel->home_url ?? '',
					'homeURLTracked' => $channel->home_url_tracked ?? '',
					'shortDesc' => $channel->short_desc ?? '',
					'longDesc' => $channel->long_desc ?? '',
				];
			}
		} else {
			$this->errorHandler->index(
				$this->errorHandler->getErrorMessage('NO_CHANNEL_DATA')
			);
		}
	}
}