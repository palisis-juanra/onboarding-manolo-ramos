<?php

namespace Services;

use Controllers\ErrorHandlerController;
use Helpers\ErrorHandlerHelper;
use Helpers\HttpRequestsHelper;
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
	}

	public function submitChannelPick(): void
	{
		if ($_SERVER["REQUEST_METHOD"] == HttpRequestsHelper::getVerb('POST')) {
			if (isset($_POST['channelList'])) {
				$this->redisClient->storeItemInRedis('currentChannelID', $_POST['channelList'], RedisInstanceHelper::REDIS_TYPE_STRING);
				// TODO: check if this is the best place to trigger a redirection
				RedirectionHelper::headerRedirection('/tourList/');
			} else {
				$this->errorHandler->index(
					ErrorHandlerHelper::getErrorMessages('POST_NO_CHANNEL_ID')
				);
			}
		}
	}

	public function renderChannelListPage(): void
	{
		// Generate template data
		$this->retrieveChannels();
		$this->buildChannelsTemplateData($this->channelList);

		// Render the template
		$this->templateRenderer->renderTemplate('dashboard/channelListPage', ['templateData' => $this->templateData]);
	}

	private function retrieveChannels(): void
	{
		// TODO: add error handling for empty channel results
		$this->channelList = $this->tourCMSclient->list_channels();
	}
	
	private function buildChannelsTemplateData(object $channelList): void
	{
		if (isset($channelList->channel)) {
			foreach($channelList->channel as $channel) {
				$this->templateData[] = [
					'channelId' => $channel->channel_id ?? '',
					'accountId' => $channel->account_id ?? '',
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
			// TODO: add error handling for empty channel results
			error_log("No channel data available!", 0);
			return;
		}
	}
}