<?php

namespace Services;

class ChannelListHandlerService 
{
	// Service instances
	private $tourCMSclient;
	private $templateRenderer;

	// Member variables
	private $channelList;
	private array $templateData;

	public function __construct($tourCMSclient, $templateRenderer)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->templateRenderer = $templateRenderer;
	}

	public function submitChannelPick(): void
	{
		
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