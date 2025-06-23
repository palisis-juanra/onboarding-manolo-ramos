<?php

namespace Services;

use Services\TemplateRendererService;

class ChannelListHandlerService 
{
	// Service instances
	private $tourCMS;
	private $templateRenderer;

	// Member variables
	private $channelList;

	public function __constuct($tourCMS)
	{
		$this->tourCMS = $tourCMS;
		$this->templateRenderer = new TemplateRendererService();
	}

	public function showChannelList()
	{
		$this->channelList = $this->retrieveChannels();
	}

	private function retrieveChannels()
	{
		
	}

	public function submitChannelPick()
	{
		
	}

	public function renderChannelListPage()
	{
		// TODO: pass on the channel list data array to the template render.
		$this->templateRenderer->renderTemplate('channelList/channelListPage', []);
	}
}