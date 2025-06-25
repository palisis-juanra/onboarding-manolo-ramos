<?php

namespace Services;

use Helpers\RedisInstanceHelper;
use TourCMS\Utils\TourCMS;

class ChannelListHandlerService 
{
	// Service instances
	private $tourCMSclient;
	private $templateRenderer;
	private $redisClient;

	// Member variables
	private $channelList;
	private array $templateData;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer
	)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->redisClient = $redisClient;
		$this->templateRenderer = $templateRenderer;
	}

	public function submitChannelPick(): void
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if(isset($_POST['channelList'])) {
				// TODO: save on redis
				print $_POST['channelList'];
				$this->redisClient->storeItemInRedis('currentChannelID', $_POST['channelList'], RedisInstanceHelper::REDIS_TYPE_STRING);
			}
		}
	}

	public function renderChannelListPage(): void
	{
		// Generate template data
		$this->retrieveChannels();
		$this->buildChannelsTemplateData();

		// Render the template
		$this->templateRenderer->renderTemplate('dashboard/channelListPage', ['templateData' => $this->templateData]);
	}

	private function retrieveChannels(): void
	{
		$this->channelList = $this->tourCMSclient->list_channels();
	}
	
	private function buildChannelsTemplateData(): void
	{
		if (isset($this->channelList->channel)) {
			foreach($this->channelList->channel as $channel) {
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
			error_log("No channel data available!", 0);
			return;
		}
	}
}