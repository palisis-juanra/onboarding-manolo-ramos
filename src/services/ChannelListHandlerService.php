<?php

namespace Services;

class ChannelListHandlerService 
{
	// Service instances
	private $tourCMSclient;
	private $templateRenderer;

	// Member variables
	private $channelList;

	public function __constuct($tourCMSclient, $templateRenderer)
	{
		$this->tourCMSclient = $tourCMSclient;
		$this->templateRenderer = $templateRenderer;
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