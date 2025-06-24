<?php

namespace Controllers;

use Services\ChannelListHandlerService;

class ChannelListController 
{
	// Service instances
	protected $channelListHandler;

	public function __construct($tourCMSclient,$redisClient, $templateRenderer)
	{
		$this->channelListHandler = new ChannelListHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer
		);
	}

	public function renderChannelListPage()
	{
		$this->channelListHandler->renderChannelListPage();
	}

	public function submitChannelPick()
	{
		$this->channelListHandler->submitChannelPick();
	}
}