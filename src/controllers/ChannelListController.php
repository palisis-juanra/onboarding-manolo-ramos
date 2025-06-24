<?php

namespace Controllers;

use Services\ChannelListHandlerService;

class ChannelListController 
{
	// Service instances
	protected $channelListHandler;

	public function __construct($tourCMSclient, $templateRenderer)
	{
		$this->channelListHandler = new ChannelListHandlerService(
			$tourCMSclient,
			$templateRenderer
		);
	}

	public function showChannelList()
	{
		$this->channelListHandler->showChannelList();
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